<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $campaign_id
 * @property int $maillist_id
 * @property int|null $segment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Campaign\Campaign $campaign
 * @property-read \App\Models\Campaign\CampaignMaillist|null $mailList
 * @property-read \App\Models\Campaign\CampaignSegment|null $segment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereMaillistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereSegmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignListsSegment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CampaignListsSegment extends Model
{

    protected $table = "campaigns_lists_segments";

    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign\Campaign');
    }

    public function mailList()
    {
        return $this->belongsTo('App\Models\Campaign\CampaignMaillist');
    }

    public function segment()
    {
        return $this->belongsTo('App\Models\Campaign\CampaignSegment');
    }

}
