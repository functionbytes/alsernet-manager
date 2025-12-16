<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * WarehouseLayoutParser
 *
 * Parses LAYOUT_SPEC JavaScript structure and converts to database-ready format
 * Handles position calculations with fromPrev dependencies
 */
class WarehouseLayoutParser
{
    private const WAREHOUSE_WIDTH_M = 42.23;
    private const WAREHOUSE_HEIGHT_M = 30.26;

    private array $processedSections = [];
    private array $sectionPositions = [];
    private array $errors = [];
    private array $warnings = [];

    /**
     * Parse complete LAYOUT_SPEC array
     *
     * @param array $layoutSpec
     * @return array ['floors', 'locations', 'sections', 'summary', 'errors', 'warnings']
     */
    public function parse(array $layoutSpec): array
    {
        $floors = $this->extractFloors($layoutSpec);
        $locations = [];
        $sections = [];

        // Process sections in order, respecting fromPrev dependencies
        foreach ($layoutSpec as $section) {
            $sectionId = $section['id'] ?? null;

            if (!$sectionId) {
                $this->errors[] = "Section without ID found";
                continue;
            }

            // Calculate base position for this section
            $basePosition = $this->calculateSectionPosition($section, $layoutSpec);

            // Parse locations and sections
            $parsed = $this->parseSection($section, $basePosition);

            $locations = array_merge($locations, $parsed['locations']);
            $sections = array_merge($sections, $parsed['sections']);

            // Store this section's position for fromPrev references
            $this->sectionPositions[$sectionId] = $basePosition;
        }

        // Generate summary
        $summary = $this->generateSummary($floors, $locations, $sections);

        return [
            'floors' => $floors,
            'locations' => $locations,
            'sections' => $sections,
            'summary' => $summary,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Extract floor definitions from LAYOUT_SPEC
     */
    private function extractFloors(array $layoutSpec): array
    {
        $floorSet = [];

        foreach ($layoutSpec as $section) {
            if (isset($section['floors']) && is_array($section['floors'])) {
                foreach ($section['floors'] as $floorNum) {
                    $floorSet[$floorNum] = true;
                }
            }
        }

        $floors = [];
        foreach (array_keys($floorSet) as $floorNum) {
            $floors[] = $this->mapFloorNumberToData($floorNum);
        }

        return $floors;
    }

    /**
     * Map floor number to database structure
     */
    private function mapFloorNumberToData(int $floorNum): array
    {
        $mapping = [
            1 => ['level' => 1, 'code' => 'PS0', 'name' => 'PISO 0', 'description' => 'Planta Baja'],
            2 => ['level' => 2, 'code' => 'PS1', 'name' => 'PISO 1', 'description' => 'Primer piso'],
            3 => ['level' => 3, 'code' => 'PS2', 'name' => 'PISO 2', 'description' => 'Segundo piso'],
        ];

        return $mapping[$floorNum] ?? [
            'level' => $floorNum,
            'code' => "PS{$floorNum}",
            'name' => "PISO {$floorNum}",
            'description' => "Piso {$floorNum}",
        ];
    }

    /**
     * Calculate base position for a section
     */
    private function calculateSectionPosition(array $section, array $layoutSpec): array
    {
        $sectionId = $section['id'];
        $anchor = $section['anchor'] ?? 'top-right';

        // Check if has start position
        if (isset($section['start'])) {
            return $this->calculateFromStart($section['start'], $anchor);
        }

        // Check if has fromPrev reference
        if (isset($section['fromPrev'])) {
            return $this->calculateFromPrev($section, $layoutSpec);
        }

        // Default position (top-left)
        $this->warnings[] = "Section {$sectionId} has no position data, using default (0, 0)";
        return ['x' => 0, 'y' => 0, 'anchor' => $anchor];
    }

    /**
     * Calculate position from start offsets
     */
    private function calculateFromStart(array $start, string $anchor): array
    {
        $x = 0;
        $y = 0;

        switch ($anchor) {
            case 'top-right':
                $x = self::WAREHOUSE_WIDTH_M - ($start['offsetRight_m'] ?? 0);
                $y = $start['offsetTop_m'] ?? 0;
                break;

            case 'top-left':
                $x = $start['offsetLeft_m'] ?? 0;
                $y = $start['offsetTop_m'] ?? 0;
                break;

            case 'bottom-right':
                $x = self::WAREHOUSE_WIDTH_M - ($start['offsetRight_m'] ?? 0);
                $y = self::WAREHOUSE_HEIGHT_M - ($start['offsetBottom_m'] ?? 0);
                break;

            case 'bottom-left':
                $x = $start['offsetLeft_m'] ?? 0;
                $y = self::WAREHOUSE_HEIGHT_M - ($start['offsetBottom_m'] ?? 0);
                break;
        }

        return ['x' => $x, 'y' => $y, 'anchor' => $anchor];
    }

    /**
     * Calculate position from previous section reference
     */
    private function calculateFromPrev(array $section, array $layoutSpec): array
    {
        $fromPrev = $section['fromPrev'];
        $refSectionId = $fromPrev['sectionId'] ?? null;
        $mode = $fromPrev['mode'] ?? null;
        $gap_m = $fromPrev['gap_m'] ?? 0;

        if (!$refSectionId || !isset($this->sectionPositions[$refSectionId])) {
            $this->errors[] = "Section {$section['id']} references non-existent section {$refSectionId}";
            return ['x' => 0, 'y' => 0, 'anchor' => $section['anchor'] ?? 'top-right'];
        }

        $refPos = $this->sectionPositions[$refSectionId];
        $refSection = $this->findSectionById($refSectionId, $layoutSpec);

        if (!$refSection) {
            return ['x' => 0, 'y' => 0, 'anchor' => $section['anchor'] ?? 'top-right'];
        }

        $refWidth = ($refSection['shelf']['w_m'] ?? 1.0);
        $refHeight = ($refSection['shelf']['h_m'] ?? 1.0);

        // Calculate based on mode
        $x = $refPos['x'];
        $y = $refPos['y'];

        switch ($mode) {
            case 'leftOf':
                $x = $refPos['x'] - $refWidth - $gap_m;
                break;

            case 'rightOf':
                $x = $refPos['x'] + $refWidth + $gap_m;
                break;

            case 'below':
                $y = $refPos['y'] + $refHeight + $gap_m;
                break;

            case 'above':
                $y = $refPos['y'] - $refHeight - $gap_m;
                break;
        }

        return ['x' => $x, 'y' => $y, 'anchor' => $section['anchor'] ?? 'top-right'];
    }

    /**
     * Find section by ID in layout spec
     */
    private function findSectionById(string $sectionId, array $layoutSpec): ?array
    {
        foreach ($layoutSpec as $section) {
            if (($section['id'] ?? null) === $sectionId) {
                return $section;
            }
        }
        return null;
    }

    /**
     * Parse a single section into locations and sections
     */
    private function parseSection(array $section, array $basePosition): array
    {
        $sectionId = $section['id'];
        $kind = $section['kind'] ?? 'row';
        $floors = $section['floors'] ?? [1];

        $locations = [];
        $sections = [];

        // Parse itemLocationsByIndex structure
        if (isset($section['itemLocationsByIndex'])) {
            $parsed = $this->parseItemLocationsByIndex(
                $section['itemLocationsByIndex'],
                $sectionId,
                $basePosition,
                $section,
                $floors
            );
            $locations = array_merge($locations, $parsed['locations']);
            $sections = array_merge($sections, $parsed['sections']);
        }

        // Parse locationsByRow structure
        if (isset($section['locationsByRow'])) {
            $parsed = $this->parseLocationsByRow(
                $section['locationsByRow'],
                $sectionId,
                $basePosition,
                $section,
                $floors
            );
            $locations = array_merge($locations, $parsed['locations']);
            $sections = array_merge($sections, $parsed['sections']);
        }

        return ['locations' => $locations, 'sections' => $sections];
    }

    /**
     * Parse itemLocationsByIndex structure
     */
    private function parseItemLocationsByIndex(
        array $itemLocationsByIndex,
        string $sectionId,
        array $basePosition,
        array $section,
        array $floors
    ): array {
        $locations = [];
        $sections = [];

        $shelf_w = $section['shelf']['w_m'] ?? 1.0;
        $shelf_h = $section['shelf']['h_m'] ?? 1.0;
        $kind = $section['kind'] ?? 'row';
        $direction = $section['direction'] ?? 'right';

        // Calculate gaps
        $gapBetween = 0;
        if ($kind === 'row') {
            $gapBetween = $section['gaps']['between_m'] ?? 0;
        } elseif ($kind === 'columns') {
            $gapBetween = $section['gaps']['betweenRows_m'] ?? 0;
        }

        foreach ($itemLocationsByIndex as $index => $faceGroups) {
            $locationCode = "{$sectionId}-{$index}";

            // Calculate position for this location
            $pos = $this->calculateLocationPosition(
                $basePosition,
                $index,
                $shelf_w,
                $shelf_h,
                $gapBetween,
                $kind,
                $direction
            );

            // Extract sections
            $locationSections = [];
            $faces = [];
            $maxLevel = 0;

            foreach ($faceGroups as $face => $sectionList) {
                $faces[] = $face;

                foreach ($sectionList as $sectionData) {
                    $code = $sectionData['code'] ?? null;
                    if (!$code) continue;

                    $level = $this->extractLevelFromCode($code);
                    $maxLevel = max($maxLevel, $level);

                    $locationSections[] = [
                        'code' => $code,
                        'face' => $face,
                        'level' => $level,
                        'barcode' => null,
                    ];
                }
            }

            // Determine style based on faces
            $style = $this->determineStyle($faces);

            $locations[] = [
                'code' => $locationCode,
                'floor_level' => $floors[0] ?? 1,
                'style_code' => $style,
                'visual_position_x' => round($pos['x'], 2),
                'visual_position_y' => round($pos['y'], 2),
                'visual_width_m' => $shelf_w,
                'visual_height_m' => $shelf_h,
                'total_levels' => $maxLevel,
                'sections' => $locationSections,
            ];

            $sections = array_merge($sections, $locationSections);
        }

        return ['locations' => $locations, 'sections' => $sections];
    }

    /**
     * Parse locationsByRow structure
     */
    private function parseLocationsByRow(
        array $locationsByRow,
        string $sectionId,
        array $basePosition,
        array $section,
        array $floors
    ): array {
        // Similar to parseItemLocationsByIndex but handles row structure
        return $this->parseItemLocationsByIndex(
            $locationsByRow,
            $sectionId,
            $basePosition,
            $section,
            $floors
        );
    }

    /**
     * Calculate position for individual location within a section
     */
    private function calculateLocationPosition(
        array $basePosition,
        int $index,
        float $shelf_w,
        float $shelf_h,
        float $gap,
        string $kind,
        string $direction
    ): array {
        $x = $basePosition['x'];
        $y = $basePosition['y'];

        if ($kind === 'row') {
            // Horizontal arrangement
            if ($direction === 'right') {
                $x += ($index - 1) * ($shelf_w + $gap);
            } else {
                $x -= ($index - 1) * ($shelf_w + $gap);
            }
        } elseif ($kind === 'columns') {
            // Vertical arrangement
            if ($direction === 'down') {
                $y += ($index - 1) * ($shelf_h + $gap);
            } else {
                $y -= ($index - 1) * ($shelf_h + $gap);
            }
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * Extract level from section code
     * Format: X-PP-S-M-N where N is level
     */
    private function extractLevelFromCode(string $code): int
    {
        $parts = explode('-', $code);
        return (int)end($parts);
    }

    /**
     * Determine style code based on faces present
     */
    private function determineStyle(array $faces): string
    {
        $uniqueFaces = array_unique($faces);
        sort($uniqueFaces);

        if (count($uniqueFaces) === 1) {
            switch ($uniqueFaces[0]) {
                case 'right':
                    return 'CAR-1';
                case 'left':
                    return 'CAR-2';
                case 'front':
                    return 'CARD';
                case 'back':
                    return 'CAF';
            }
        }

        if (count($uniqueFaces) === 2 && in_array('right', $uniqueFaces) && in_array('left', $uniqueFaces)) {
            return 'PAS';
        }

        if (count($uniqueFaces) >= 3) {
            return 'ISL';
        }

        return 'PAS'; // Default
    }

    /**
     * Generate summary statistics
     */
    private function generateSummary(array $floors, array $locations, array $sections): array
    {
        $floorBreakdown = [];
        $styleBreakdown = [];

        foreach ($locations as $location) {
            $floorLevel = $location['floor_level'];
            $styleCode = $location['style_code'];

            $floorBreakdown[$floorLevel] = ($floorBreakdown[$floorLevel] ?? 0) + 1;
            $styleBreakdown[$styleCode] = ($styleBreakdown[$styleCode] ?? 0) + 1;
        }

        return [
            'total_floors' => count($floors),
            'total_locations' => count($locations),
            'total_sections' => count($sections),
            'floor_breakdown' => $floorBreakdown,
            'style_breakdown' => $styleBreakdown,
        ];
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get warnings
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
