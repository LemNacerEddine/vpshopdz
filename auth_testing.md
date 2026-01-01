# Auth-Gated App Testing Playbook

## Step 1: Create Test User & Session

```bash
mongosh --eval "
use('test_database');
var userId = 'test-user-' + Date.now();
var sessionToken = 'test_session_' + Date.now();
db.users.insertOne({
  user_id: userId,
  email: 'test.user.' + Date.now() + '@example.com',
  name: 'Test User',
  picture: 'https://via.placeholder.com/150',
  role: 'customer',
  created_at: new Date()
});
db.user_sessions.insertOne({
  user_id: userId,
  session_token: sessionToken,
  expires_at: new Date(Date.now() + 7*24*60*60*1000),
  created_at: new Date()
});
print('Session token: ' + sessionToken);
print('User ID: ' + userId);
"
```

## Step 2: Create Admin Test User

```bash
mongosh --eval "
use('test_database');
var userId = 'admin-test-' + Date.now();
var sessionToken = 'admin_session_' + Date.now();
db.users.insertOne({
  user_id: userId,
  email: 'admin.test@agroyousfi.dz',
  name: 'Admin Test',
  role: 'admin',
  created_at: new Date()
});
db.user_sessions.insertOne({
  user_id: userId,
  session_token: sessionToken,
  expires_at: new Date(Date.now() + 7*24*60*60*1000),
  created_at: new Date()
});
print('Admin Session token: ' + sessionToken);
print('Admin User ID: ' + userId);
"
```

## Step 3: Test Backend API

```bash
# Test auth endpoint
curl -X GET "https://your-app.com/api/auth/me" \
  -H "Authorization: Bearer YOUR_SESSION_TOKEN"

# Test products
curl -X GET "https://your-app.com/api/products"

# Test categories
curl -X GET "https://your-app.com/api/categories"

# Test admin endpoints
curl -X GET "https://your-app.com/api/admin/stats" \
  -H "Authorization: Bearer ADMIN_SESSION_TOKEN"
```

## Step 4: Browser Testing with Playwright

```javascript
// Set cookie and navigate
await page.context().addCookies([{
    name: "session_token",
    value: "YOUR_SESSION_TOKEN",
    domain: "your-app.com",
    path: "/",
    httpOnly: true,
    secure: true,
    sameSite: "None"
}]);
await page.goto("https://your-app.com");
```

## Checklist

- [ ] User document has user_id field (custom UUID)
- [ ] Session user_id matches user's user_id exactly
- [ ] All queries use `{"_id": 0}` projection
- [ ] API returns user data with user_id field
- [ ] Browser loads dashboard (not login page)
- [ ] Products display correctly
- [ ] Cart functionality works
- [ ] Checkout flow works

## Success Indicators

✅ /api/auth/me returns user data
✅ Products load on home page
✅ Cart operations work
✅ Orders can be placed
