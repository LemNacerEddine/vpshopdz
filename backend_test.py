import requests
import sys
import json
from datetime import datetime

class AgroYousfiAPITester:
    def __init__(self, base_url="https://farmers-bazaar.preview.emergentagent.com/api"):
        self.base_url = base_url
        self.session_token = None
        self.admin_token = None
        self.tests_run = 0
        self.tests_passed = 0
        self.test_results = []

    def log_test(self, name, success, details=""):
        """Log test result"""
        self.tests_run += 1
        if success:
            self.tests_passed += 1
        
        result = {
            "test": name,
            "success": success,
            "details": details,
            "timestamp": datetime.now().isoformat()
        }
        self.test_results.append(result)
        
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} - {name}")
        if details:
            print(f"    {details}")

    def run_test(self, name, method, endpoint, expected_status, data=None, headers=None):
        """Run a single API test"""
        url = f"{self.base_url}/{endpoint}"
        test_headers = {'Content-Type': 'application/json'}
        
        if headers:
            test_headers.update(headers)
        
        if self.session_token:
            test_headers['Authorization'] = f'Bearer {self.session_token}'

        try:
            if method == 'GET':
                response = requests.get(url, headers=test_headers)
            elif method == 'POST':
                response = requests.post(url, json=data, headers=test_headers)
            elif method == 'PUT':
                response = requests.put(url, json=data, headers=test_headers)
            elif method == 'DELETE':
                response = requests.delete(url, headers=test_headers)

            success = response.status_code == expected_status
            details = f"Status: {response.status_code}"
            
            if not success:
                details += f" (Expected: {expected_status})"
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:100]}"
            
            self.log_test(name, success, details)
            
            if success:
                try:
                    return response.json()
                except:
                    return {}
            return None

        except Exception as e:
            self.log_test(name, False, f"Exception: {str(e)}")
            return None

    def test_seed_database(self):
        """Test database seeding"""
        return self.run_test("Seed Database", "POST", "seed", 200)

    def test_get_categories(self):
        """Test getting categories"""
        return self.run_test("Get Categories", "GET", "categories", 200)

    def test_get_products(self):
        """Test getting products"""
        return self.run_test("Get Products", "GET", "products", 200)

    def test_get_featured_products(self):
        """Test getting featured products"""
        return self.run_test("Get Featured Products", "GET", "products?featured=true", 200)

    def test_search_products(self):
        """Test product search"""
        return self.run_test("Search Products", "GET", "products?search=قمح", 200)

    def test_get_wilayas(self):
        """Test getting Algerian wilayas"""
        return self.run_test("Get Wilayas", "GET", "wilayas", 200)

    def test_send_otp(self):
        """Test sending OTP"""
        test_email = f"test_{datetime.now().strftime('%H%M%S')}@test.com"
        data = {"email": test_email}
        result = self.run_test("Send OTP", "POST", "auth/send-otp", 200, data)
        
        if result and 'demo_code' in result:
            # Test OTP verification
            verify_data = {"email": test_email, "code": result['demo_code']}
            verify_result = self.run_test("Verify OTP", "POST", "auth/verify-otp", 200, verify_data)
            
            if verify_result and 'session_token' in verify_result:
                self.session_token = verify_result['session_token']
                return verify_result
        
        return result

    def test_get_current_user(self):
        """Test getting current authenticated user"""
        if not self.session_token:
            self.log_test("Get Current User", False, "No session token available")
            return None
        return self.run_test("Get Current User", "GET", "auth/me", 200)

    def test_cart_operations(self):
        """Test cart operations"""
        # Get cart (should be empty initially)
        cart = self.run_test("Get Empty Cart", "GET", "cart", 200)
        
        # Get a product to add to cart
        products = self.run_test("Get Products for Cart", "GET", "products?limit=1", 200)
        if not products or not products:
            self.log_test("Cart Operations", False, "No products available for cart testing")
            return
        
        product_id = products[0]['product_id']
        
        # Add item to cart
        add_data = {"product_id": product_id, "quantity": 2}
        self.run_test("Add to Cart", "POST", "cart/add", 200, add_data)
        
        # Get cart with items
        self.run_test("Get Cart with Items", "GET", "cart", 200)
        
        # Update cart item
        update_data = {"product_id": product_id, "quantity": 3}
        self.run_test("Update Cart Item", "PUT", "cart/update", 200, update_data)
        
        # Remove item from cart
        self.run_test("Remove from Cart", "DELETE", f"cart/remove/{product_id}", 200)

    def test_admin_login(self):
        """Test admin login"""
        # Send OTP to admin email
        admin_email = "admin@agroyousfi.dz"
        data = {"email": admin_email}
        result = self.run_test("Send Admin OTP", "POST", "auth/send-otp", 200, data)
        
        if result and 'demo_code' in result:
            # Verify admin OTP
            verify_data = {"email": admin_email, "code": result['demo_code']}
            verify_result = self.run_test("Verify Admin OTP", "POST", "auth/verify-otp", 200, verify_data)
            
            if verify_result and 'session_token' in verify_result:
                self.admin_token = verify_result['session_token']
                return verify_result
        
        return result

    def test_admin_operations(self):
        """Test admin operations"""
        if not self.admin_token:
            self.log_test("Admin Operations", False, "No admin token available")
            return
        
        # Temporarily switch to admin token
        original_token = self.session_token
        self.session_token = self.admin_token
        
        # Test admin stats
        stats_result = self.run_test("Get Admin Stats", "GET", "admin/stats", 200)
        if stats_result:
            required_fields = ['total_products', 'total_orders', 'pending_orders', 'total_users', 'total_revenue']
            missing_fields = [field for field in required_fields if field not in stats_result]
            if missing_fields:
                self.log_test("Admin Stats Fields", False, f"Missing fields: {missing_fields}")
            else:
                self.log_test("Admin Stats Fields", True, "All required fields present")
        
        # Test admin orders
        orders_result = self.run_test("Get Admin Orders", "GET", "admin/orders", 200)
        
        # Test order status update if we have orders
        if orders_result and len(orders_result) > 0:
            order_id = orders_result[0]['order_id']
            status_data = {"status": "confirmed"}
            self.run_test("Update Order Status", "PUT", f"admin/orders/{order_id}/status", 200, status_data)
        
        # Restore original token
        self.session_token = original_token

    def test_phone_authentication_flow(self):
        """Test complete phone authentication flow"""
        # Test phone number for registration - use timestamp to make it unique
        test_phone = f"0555{datetime.now().strftime('%H%M%S')}"
        
        # Step 1: Send OTP to phone
        phone_data = {"phone": test_phone}
        otp_result = self.run_test("Send Phone OTP", "POST", "auth/phone/send-otp", 200, phone_data)
        
        if not otp_result or 'demo_code' not in otp_result:
            self.log_test("Phone Authentication Flow", False, "Failed to get OTP code")
            return None
        
        # Step 2: Verify OTP (should return new_user status for new phone)
        verify_data = {"phone": test_phone, "code": otp_result['demo_code']}
        verify_result = self.run_test("Verify Phone OTP (New User)", "POST", "auth/phone/verify-otp", 200, verify_data)
        
        if not verify_result:
            self.log_test("Phone Authentication Flow", False, "Failed to verify OTP")
            return None
        
        if verify_result.get('status') == 'new_user':
            # Step 3: Complete registration
            register_data = {
                "phone": test_phone,
                "name": "أحمد محمد",
                "wilaya": "16 - الجزائر (Alger)",
                "address": "شارع الاستقلال، الجزائر العاصمة"
            }
            register_result = self.run_test("Complete Phone Registration", "POST", "auth/phone/register", 200, register_data)
            
            if register_result and 'session_token' in register_result:
                self.session_token = register_result['session_token']
                self.log_test("Phone Authentication Flow", True, "New user registration completed")
                return register_result
        
        elif verify_result.get('status') == 'existing_user':
            # User already exists, we got session token
            if 'session_token' in verify_result:
                self.session_token = verify_result['session_token']
                self.log_test("Phone Authentication Flow", True, "Existing user login completed")
                return verify_result
        
        self.log_test("Phone Authentication Flow", False, f"Unexpected status: {verify_result.get('status')}")
        return None

    def test_link_email_feature(self):
        """Test linking email to phone account"""
        if not self.session_token:
            self.log_test("Link Email Feature", False, "No authenticated session")
            return
        
        # Test linking email
        link_data = {"email": f"linked_{datetime.now().strftime('%H%M%S')}@test.com"}
        return self.run_test("Link Email to Phone Account", "POST", "auth/link-email", 200, link_data)

    def test_wishlist_operations(self):
        """Test wishlist operations (requires authentication)"""
        if not self.session_token:
            self.log_test("Wishlist Operations", False, "No authenticated session")
            return
        
        # Get a product for wishlist testing
        products = self.run_test("Get Products for Wishlist", "GET", "products?limit=1", 200)
        if not products or not products:
            self.log_test("Wishlist Operations", False, "No products available")
            return
        
        product_id = products[0]['product_id']
        
        # Add to wishlist
        self.run_test("Add to Wishlist", "POST", f"wishlist/{product_id}", 200)
        
        # Get wishlist
        wishlist = self.run_test("Get Wishlist", "GET", "wishlist", 200)
        
        # Remove from wishlist
        self.run_test("Remove from Wishlist", "DELETE", f"wishlist/{product_id}", 200)
        
        return wishlist

    def test_addresses_operations(self):
        """Test address management operations (requires authentication)"""
        if not self.session_token:
            self.log_test("Address Operations", False, "No authenticated session")
            return
        
        # Add new address
        address_data = {
            "title": "المنزل",
            "phone": "0555123456",
            "address": "شارع الاستقلال، حي النصر",
            "wilaya": "16 - الجزائر (Alger)",
            "isDefault": True
        }
        add_result = self.run_test("Add Address", "POST", "addresses", 200, address_data)
        
        # Get addresses
        addresses = self.run_test("Get Addresses", "GET", "addresses", 200)
        
        # Delete address if we got an address_id
        if add_result and 'address_id' in add_result:
            address_id = add_result['address_id']
            self.run_test("Delete Address", "DELETE", f"addresses/{address_id}", 200)
        
        return addresses

    def test_product_search_arabic_english(self):
        """Test product search with Arabic and English queries"""
        # Test Arabic search
        arabic_result = self.run_test("Search Products (Arabic)", "GET", "products?search=بذور", 200)
        
        # Test English search  
        english_result = self.run_test("Search Products (English)", "GET", "products?search=wheat", 200)
        
        return arabic_result, english_result

    def test_order_creation(self):
        """Test order creation"""
        # First add items to cart
        products = self.run_test("Get Products for Order", "GET", "products?limit=1", 200)
        if not products or not products:
            self.log_test("Order Creation", False, "No products available for order testing")
            return
        
        product_id = products[0]['product_id']
        add_data = {"product_id": product_id, "quantity": 1}
        self.run_test("Add to Cart for Order", "POST", "cart/add", 200, add_data)
        
        # Create order
        order_data = {
            "customer_name": "Test Customer",
            "phone": "0555123456",
            "address": "Test Address, Test City",
            "wilaya": "الجزائر",
            "notes": "Test order"
        }
        
        result = self.run_test("Create Order", "POST", "orders", 200, order_data)
        
        if result and 'order_id' in result:
            order_id = result['order_id']
            # Test getting the order
            self.run_test("Get Order", "GET", f"orders/{order_id}", 200)

    def run_all_tests(self):
        """Run all API tests"""
        print("🚀 Starting AgroYousfi API Tests...")
        print("=" * 50)
        
        # Basic endpoints
        self.test_seed_database()
        self.test_get_categories()
        self.test_get_products()
        self.test_get_featured_products()
        self.test_get_wilayas()
        
        # Product search (Arabic and English)
        self.test_product_search_arabic_english()
        
        # Phone authentication flow (NEW)
        print("\n📱 Testing Phone Authentication Flow...")
        self.test_phone_authentication_flow()
        
        # Link email feature (NEW)
        print("\n📧 Testing Link Email Feature...")
        self.test_link_email_feature()
        
        # Wishlist operations (NEW - requires auth)
        print("\n❤️ Testing Wishlist Operations...")
        self.test_wishlist_operations()
        
        # Address operations (NEW - requires auth)
        print("\n🏠 Testing Address Operations...")
        self.test_addresses_operations()
        
        # Cart operations
        print("\n🛒 Testing Cart Operations...")
        self.test_cart_operations()
        
        # Order creation
        print("\n📦 Testing Order Creation...")
        self.test_order_creation()
        
        # Email authentication (existing)
        print("\n📧 Testing Email Authentication...")
        # Reset session for email auth test
        self.session_token = None
        self.test_send_otp()
        self.test_get_current_user()
        
        # Admin operations
        print("\n👑 Testing Admin Operations...")
        self.test_admin_login()
        self.test_admin_operations()
        
        # Print summary
        print("\n" + "=" * 50)
        print(f"📊 Test Summary: {self.tests_passed}/{self.tests_run} tests passed")
        
        if self.tests_passed == self.tests_run:
            print("🎉 All tests passed!")
            return 0
        else:
            print(f"⚠️  {self.tests_run - self.tests_passed} tests failed")
            return 1

def main():
    tester = AgroYousfiAPITester()
    return tester.run_all_tests()

if __name__ == "__main__":
    sys.exit(main())