# Laravel Reverb Multi-Tenant Setup

## Overview
This document explains how Laravel Reverb is configured for multi-tenant real-time broadcasting in this application. Each tenant subdomain can connect to the WebSocket server and receive tenant-specific broadcasts.

## Architecture

### Tenant Isolation
- Each tenant connects using their subdomain (e.g., `tenant1.quickjuan.test`)
- Broadcasting channels are tenant-scoped automatically via Stancl Tenancy
- Authorization happens within tenant context using `tenant-channels.php`

### Key Components

1. **Tenant Channels** (`routes/tenant-channels.php`)
   - Defines tenant-specific broadcast channels
   - Channels include: pending-orders, branch, kitchen
   - Authorization uses tenant-aware user relationships

2. **Broadcasting Configuration** (`config/reverb.php`)
   - Single Reverb server handles all tenants
   - Origin validation allows all tenant subdomains
   - Uses pattern matching for subdomain verification

3. **Frontend Echo** (`resources/js/bootstrap.js`)
   - Dynamically detects current tenant subdomain
   - Connects to Reverb using tenant's hostname
   - Automatically includes CSRF token for auth

4. **Tenancy Provider** (`app/Providers/TenancyServiceProvider.php`)
   - Loads tenant broadcast channels when tenancy is initialized
   - Registers broadcasting routes with tenant middleware
   - Ensures channels are only accessible within tenant context

## Configuration

### Environment Variables

```env
# Broadcasting Driver
BROADCAST_CONNECTION=reverb

# Reverb Server Configuration
REVERB_APP_ID=12345
REVERB_APP_KEY=quickjuan
REVERB_APP_SECRET=quickjuan-secret
REVERB_HOST=localhost              # Or your domain
REVERB_PORT=8080                   # Development port
REVERB_SCHEME=http                 # Use 'https' in production

# Frontend Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Production Setup

For production, update these settings:

```env
REVERB_HOST=yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

## Starting the Reverb Server

### Development
```bash
# Start everything (recommended)
composer dev

# Or start Reverb separately
php artisan reverb:start --debug
```

### Production
```bash
# Run as daemon
php artisan reverb:start --host=0.0.0.0 --port=8080
```

## How It Works

### 1. Tenant Connection Flow

```
1. User visits tenant1.quickjuan.test
2. Frontend Echo initializes with tenant hostname
3. WebSocket connects to ws://tenant1.quickjuan.test:8080
4. Tenancy middleware identifies tenant from subdomain
5. User authenticates via /api/broadcasting/auth endpoint
6. Tenant-specific channels become available
```

### 2. Broadcasting an Event

```php
use App\Events\OrderPlaced;

// In your tenant context (e.g., CartService)
broadcast(new OrderPlaced($order, $branchId))->toOthers();
```

### 3. Listening on Frontend

```javascript
// In your Vue component
const branchId = ref(123);

// Subscribe to tenant-specific channel
window.Echo.channel(`pending-orders.${branchId.value}`)
    .listen('order-placed', (data) => {
        console.log('New order received:', data);
        // Update UI
    });

// Cleanup
onUnmounted(() => {
    window.Echo.leave(`pending-orders.${branchId.value}`);
});
```

## Available Channels

### 1. Pending Orders Channel
```
Channel: pending-orders.{branchId}
Authorization: User must belong to the branch
Events: order-placed, order-updated
```

### 2. Branch Channel
```
Channel: branch.{branchId}
Authorization: User must have access to branch
Events: Branch-specific notifications
```

### 3. Kitchen Display Channel
```
Channel: kitchen.{branchId}
Authorization: User must have kitchen access to branch
Events: Real-time kitchen updates
```

### 4. User Private Channel
```
Channel: App.Models.User.{userId}
Authorization: User can only access their own channel
Events: User-specific notifications
```

## Troubleshooting

### Issue: WebSocket Connection Failed

**Check:**
1. Reverb server is running: `ps aux | grep reverb`
2. Port 8080 is open and not blocked by firewall
3. Browser console for connection errors

**Solution:**
```bash
# Restart Reverb with debug mode
php artisan reverb:restart --debug
```

### Issue: Channel Authorization Failed

**Check:**
1. User is authenticated
2. CSRF token is present in meta tag
3. User has access to the branch

**Solution:**
- Verify the authorization logic in `routes/tenant-channels.php`
- Check that user has proper branch relationships
- Ensure tenant context is initialized

### Issue: Events Not Broadcasting

**Check:**
1. `BROADCAST_CONNECTION=reverb` in `.env`
2. Event implements `ShouldBroadcast` interface
3. Event's `broadcastOn()` returns correct channel

**Debug:**
```bash
# Check Reverb logs
php artisan reverb:start --debug

# Test broadcasting
php artisan tinker
> broadcast(new YourEvent());
```

### Issue: Wrong Tenant Receiving Events

**Problem:** Events broadcasting to all tenants instead of specific tenant

**Solution:**
- Ensure tenancy is initialized before broadcasting
- Check that channels include tenant-specific identifiers
- Verify subdomain middleware is active

## Security Considerations

### Production Checklist

1. **Restrict Allowed Origins**
   ```php
   // In config/reverb.php
   'allowed_origins' => [
       'https://tenant1.yourdomain.com',
       'https://tenant2.yourdomain.com',
   ],
   ```

2. **Use HTTPS/WSS**
   ```env
   REVERB_SCHEME=https
   REVERB_PORT=443
   ```

3. **Implement Rate Limiting**
   - Add throttling to broadcast auth endpoint
   - Limit connections per tenant

4. **Channel Authorization**
   - Always verify user permissions in channel callbacks
   - Never expose sensitive data in public channels

5. **Monitor Connection Limits**
   ```env
   REVERB_APP_MAX_CONNECTIONS=1000
   ```

## Performance Tips

1. **Connection Pooling**
   - Single Reverb server can handle multiple tenants
   - Scale horizontally if needed

2. **Redis Scaling**
   ```env
   REVERB_SCALING_ENABLED=true
   REVERB_SCALING_CHANNEL=reverb
   ```

3. **Optimize Broadcasts**
   - Use `toOthers()` to exclude sender
   - Implement queue-based broadcasting for heavy operations
   - Only broadcast necessary data

## Testing

### Manual Testing

```bash
# Terminal 1: Start Reverb
php artisan reverb:start --debug

# Terminal 2: Watch logs
php artisan pail

# Browser: Open two tenant windows
# - tenant1.quickjuan.test (Kitchen Display)
# - tenant1.quickjuan.test (POS)
# Create order in POS, should appear instantly in Kitchen Display
```

### Automated Testing

```php
use Illuminate\Support\Facades\Event;

test('order placed broadcasts to correct channel', function () {
    Event::fake([OrderPlaced::class]);
    
    // Create order
    $order = Order::factory()->create();
    
    // Assert event was broadcast
    Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
});
```

## Migration Notes

If you had a previous Reverb setup, note these changes:

1. **Central channels.php is unchanged** - used only for central domain
2. **New tenant-channels.php** - loaded only within tenant context
3. **Dynamic hostname** - frontend auto-detects tenant subdomain
4. **Origin validation** - allows all tenant subdomains automatically

## Additional Resources

- [Laravel Reverb Documentation](https://laravel.com/docs/11.x/reverb)
- [Stancl Tenancy Documentation](https://tenancyforlaravel.com)
- [Laravel Broadcasting Documentation](https://laravel.com/docs/11.x/broadcasting)

## Support

For issues specific to this implementation, check:
1. [routes/tenant-channels.php](routes/tenant-channels.php) - Channel definitions
2. [app/Providers/TenancyServiceProvider.php](app/Providers/TenancyServiceProvider.php) - Broadcasting setup
3. [resources/js/bootstrap.js](resources/js/bootstrap.js) - Frontend configuration
4. [config/reverb.php](config/reverb.php) - Server configuration
