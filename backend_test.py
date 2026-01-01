import requests
import sys
import json
from datetime import datetime

class AgroYousfiAPITester:
    def __init__(self, base_url="https://seeds-tools-store.preview.emergentagent.com/api"):
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
        self.run_test("Get Admin Stats", "GET", "admin/stats", 200)
        
        # Test admin orders
        self.run_test("Get Admin Orders", "GET", "admin/orders", 200)
        
        # Restore original token
        self.session_token = original_token

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
        self.test_search_products()
        self.test_get_wilayas()
        
        # Authentication
        self.test_send_otp()
        self.test_get_current_user()
        
        # Cart operations
        self.test_cart_operations()
        
        # Order creation
        self.test_order_creation()
        
        # Admin operations
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