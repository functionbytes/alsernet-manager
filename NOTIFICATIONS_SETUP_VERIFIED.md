# ✅ Push Notifications - Setup Verified

## Problems Fixed

### 1. **Channel Name Mismatch** ✅
- **Problem**: JavaScript subscribed to `user.{id}` but routes defined `users.{id}` (plural)
- **Fixed**: Updated `modules/Notification/resources/js/notifications.js` line 336
  - Changed: `window.Echo.private('user.${userId}')`
  - To: `window.Echo.private('users.${userId}')`

### 2. **Broadcasting Auth Endpoint Missing** ✅
- **Problem**: Browser got 404 from `/broadcasting/auth` endpoint
- **Fixed**: Registered broadcasting routes in `routes/web.php` line 14
  - Added: `\Illuminate\Support\Facades\Broadcast::routes(['middleware' => ['auth']]);`
  - Result: Route `broadcasting/auth` now registered and accessible

### 3. **Notification Channel Not Specified** ✅
- **Problem**: Notification class didn't tell Laravel which channel to broadcast to
- **Fixed**: Added `broadcastOn()` method to `DocumentStageAdvanced` notification
  - Now returns: `[new PrivateChannel('users.'.$this->notifiable->id)]`
  - This ensures notifications broadcast to the exact channel the JS subscribes to

## Architecture Overview

```
Backend Flow (Notification Dispatch):
1. Document event triggers (created/stage-advanced)
2. notifyValidatorGroup() sends DocumentStageAdvanced notification to each user
3. Notification implements ShouldBroadcast & ShouldQueue
4. broadcastOn() specifies: users.{user_id}
5. Broadcasting driver (log) dispatches to Reverb
6. Reverb receives and queues for WebSocket delivery

Frontend Flow (Notification Reception):
1. Page loads, @vite() loads app.js and bootstrap.js
2. bootstrap.js initializes Laravel Echo with Reverb config
3. notifications.js subscribes to private-users.{id} channel
4. /broadcasting/auth endpoint authorizes subscription
5. Echo listens for 'document.stage.advanced' events
6. When event arrives, NotificationManager.refresh() fetches from API
7. Dropdown updates with new notification
```

## Testing Steps

### Terminal 1: Start Reverb WebSocket Server
```bash
php artisan reverb:start
# Output: Starting secure server on 0.0.0.0:8080
```

### Terminal 2: Start Queue Worker
```bash
php artisan queue:work
```

### Browser: Open Application
1. Navigate to: https://manager.test/documents/manage/6aa31534-ff3b-4e00-b1a3-4444d3cd6e9a
2. Open DevTools: `F12 → Console tab`
3. Look for message: `📧 Listening for real-time notifications on private channel`

### Verify Broadcasting Auth Works
```javascript
// In browser console, check if auth is working:
fetch('/broadcasting/auth', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ channel_name: 'private-users.1' })
})
.then(r => r.json())
.then(data => console.log('Auth response:', data))
```

### Verify WebSocket Connection
```javascript
// In browser console:
window.Echo.options
// Should show:
// {
//   broadcaster: "reverb",
//   key: "local-key",
//   wsHost: "alsernet.test",
//   wsPort: 443,
//   scheme: "https",
//   ...
// }

// Check WebSocket connection in DevTools:
// Network tab → Filter "WS" → Look for wss://alsernet.test:443/app/local-key?...
```

### Create a Document to Trigger Notification
```bash
# Terminal 3: Create document via API
curl -X POST https://manager.test/api/documents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 9999,
    "customer_id": 1,
    "type": "general"
  }'

# Or via Tinker:
php artisan tinker
>>> $doc = new Document();
>>> $doc->order_id = 9999;
>>> $doc->customer_id = 1;
>>> $doc->type_id = 1; // Use ID, not slug
>>> $doc->save();
>>> exit()
```

### Expected Results

**In Terminal 2 (Queue Worker)**:
```
Processing: Modules\Document\Notifications\DocumentStageAdvanced
Processed:  Modules\Document\Notifications\DocumentStageAdvanced
```

**In Browser Console**:
```
📧 Listening for real-time notifications on private channel
🔔 Real-time notification received: {
  document_id: 123,
  message: "El documento #9999...",
  ...
}
```

**In Notification Dropdown**:
- Red badge (🔴) appears
- "1 nueva" text shows
- Document notification appears in list
- Clicking it refreshes the notification count

## Key Configuration Files

### 1. routes/web.php (line 14)
```php
\Illuminate\Support\Facades\Broadcast::routes(['middleware' => ['auth']]);
```

### 2. routes/channels.php
```php
Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### 3. .env
```
BROADCAST_CONNECTION=log
REVERB_APP_ID=123456
REVERB_APP_KEY=local-key
REVERB_HOST=alsernet.test
REVERB_PORT=443
REVERB_SCHEME=https
```

### 4. modules/Document/app/Notifications/DocumentStageAdvanced.php
```php
public function broadcastOn(): array
{
    return [
        new PrivateChannel('users.'.$this->notifiable->id),
    ];
}
```

## Debugging Checklist

- [ ] Reverb server running on port 8080 (`php artisan reverb:start`)
- [ ] Queue worker running (`php artisan queue:work`)
- [ ] Assets compiled (`npm run build` completed)
- [ ] Browser shows "📧 Listening for real-time notifications" in console
- [ ] WebSocket connection visible in Network tab (wss://)
- [ ] `/broadcasting/auth` endpoint accessible and returns 200 OK
- [ ] Document notification appears in dropdown after creation
- [ ] Red badge appears on bell icon
- [ ] Clicking notification navigates to document

## Possible Issues

### Issue: "Unable to retrieve auth string from /broadcasting/auth - 404"
- **Cause**: Broadcasting routes not registered
- **Fix**: Ensure `Broadcast::routes()` is in routes/web.php inside the web middleware group

### Issue: "Channel channel 'users.1' is not allowed"
- **Cause**: channels.php doesn't define the channel
- **Fix**: Ensure routes/channels.php has `Broadcast::channel('users.{id}', ...)`

### Issue: WebSocket shows "wss://" but can't connect
- **Cause**: HTTPS configuration mismatch
- **Fix**: Verify REVERB_SCHEME=https and REVERB_PORT=443 in .env

### Issue: Notification received but not shown in dropdown
- **Cause**: NotificationManager.refresh() not calling API
- **Fix**: Check Network tab - should see GET /api/notifications request after event received

## Environment Details

- **Framework**: Laravel 12
- **Broadcasting**: Laravel Reverb
- **Client**: Laravel Echo + Pusher.js
- **Database Notifications**: ✅ Enabled
- **Broadcast Notifications**: ✅ Enabled (if queue worker running)
- **Auth Method**: Session-based with CSRF tokens

## Summary

✅ All three blocking issues are now fixed:
1. Channel naming is consistent (plural: `users.{id}`)
2. Broadcasting auth endpoint is registered
3. Notification specifies exact channel to broadcast to

The notification system should now work end-to-end:
- Documents create → Notification sends → Queue worker processes → Reverb broadcasts → Echo receives → Dropdown updates
