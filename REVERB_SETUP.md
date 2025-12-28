# Laravel Reverb Setup for Real-Time Kitchen Display

## Overview
This project now uses Laravel Reverb for real-time WebSocket broadcasting. When orders are placed, they instantly appear on the kitchen display without needing to refresh the page.

## Setup Instructions

### 1. Environment Variables
The following environment variables are already configured in `.env`:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=12345
REVERB_APP_KEY=quickjuan-secret-key
REVERB_APP_SECRET=quickjuan-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 2. Starting Reverb Server
To run the full development environment with Reverb:

```bash
# Option 1: Run everything with composer dev
composer dev

# Option 2: Run Reverb separately
php artisan reverb:start
```

The Reverb server will start on `http://localhost:8080`

### 3. How It Works
- When an order is placed via CartService, it broadcasts an `OrderPlaced` event
- The event is sent to a channel named `pending-orders.{branchId}`
- The Kitchen Display page listens to this channel and automatically fetches updated orders
- No polling needed - updates appear instantly via WebSocket

### 4. Frontend Configuration
Laravel Echo is configured in `resources/js/bootstrap.js`:
- Connects to Reverb on port 8080
- Automatically subscribes to `pending-orders.{branchId}` channel
- Listens for `order-placed` events

### 5. Testing
1. Open the Kitchen Display on one browser window
2. Place an order from the POS system in another window
3. The new order should appear instantly on the Kitchen Display

## Troubleshooting

### Connection Issues
If the Kitchen Display doesn't show new orders:
1. Verify Reverb is running: `php artisan reverb:start`
2. Check browser console for connection errors
3. Ensure port 8080 is not blocked by a firewall
4. Check REVERB_HOST and REVERB_PORT in .env

### Debug Mode
To see WebSocket connections:
1. Open browser DevTools Network tab
2. Filter by "WS" to see WebSocket connections
3. Look for connections to `ws://localhost:8080`

## Performance Notes
- WebSockets are more efficient than polling
- Orders appear instantly across all connected kitchen displays
- No database queries for checking updates (pull-based instead of push-based)
