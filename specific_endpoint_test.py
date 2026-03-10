import requests
import json
from datetime import datetime

class SpecificEndpointTester:
    def __init__(self, base_url="https://algerie-shop.preview.emergentagent.com"):
        self.base_url = base_url
        self.api_url = f"{base_url}/api"
        self.session_token = None
        self.tests_run = 0
        self.tests_passed = 0

    def log_test(self, name, success, details=""):
        """Log test result"""
        self.tests_run += 1
        if success:
            self.tests_passed += 1
        
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} - {name}")
        if details:
            print(f"    {details}")

    def test_endpoint(self, name, method, endpoint, expected_status, data=None, headers=None):
        """Test a specific endpoint"""
        url = f"{self.api_url}/{endpoint}"
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

            success = response.status_code == expected_status
            details = f"Status: {response.status_code}"
            
            if not success:
                details += f" (Expected: {expected_status})"
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:100]}"
            else:
                try:
                    response_data = response.json()
                    if isinstance(response_data, list):
                        details += f" - Retrieved {len(response_data)} items"
                    elif isinstance(response_data, dict):
                        if 'message' in response_data:
                            details += f" - {response_data['message']}"
                        elif 'user' in response_data:
                            user = response_data['user']
                            details += f" - User: {user.get('name', 'N/A')}, Role: {user.get('role', 'N/A')}"
                except:
                    pass
            
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

    def test_admin_login(self):
        """Test admin login with password"""
        login_data = {
            "identifier": "admin@agroyousfi.dz",
            "password": "admin123"
        }
        
        result = self.test_endpoint("Admin Login", "POST", "auth/login", 200, login_data)
        
        if result and 'session_token' in result:
            user = result.get('user', {})
            if user.get('role') == 'admin':
                self.session_token = result['session_token']
                return result
        
        return None

    def run_specific_tests(self):
        """Run the specific tests mentioned in the review request"""
        print("🎯 Testing Specific AgroYousfi Endpoints...")
        print("=" * 60)
        
        # 1. Basic API Health
        print("\n1️⃣ Basic API Health")
        self.test_endpoint("GET /api/", "GET", "", 200)
        
        # 2. Products
        print("\n2️⃣ Products Endpoints")
        products_result = self.test_endpoint("GET /api/products", "GET", "products", 200)
        
        # Test category filtering
        self.test_endpoint("GET /api/products?category_id=cat_seeds", "GET", "products?category_id=cat_seeds", 200)
        
        # Test products on sale
        sale_products = self.test_endpoint("GET /api/products-on-sale", "GET", "products-on-sale", 200)
        
        # 3. Categories
        print("\n3️⃣ Categories Endpoint")
        categories_result = self.test_endpoint("GET /api/categories", "GET", "categories", 200)
        
        # 4. Wilayas
        print("\n4️⃣ Wilayas Endpoint")
        wilayas_result = self.test_endpoint("GET /api/wilayas", "GET", "wilayas", 200)
        
        # Verify we have 48+ wilayas
        if wilayas_result and len(wilayas_result) >= 48:
            self.log_test("Wilayas Count Check", True, f"Found {len(wilayas_result)} wilayas (expected 48+)")
        elif wilayas_result:
            self.log_test("Wilayas Count Check", False, f"Found {len(wilayas_result)} wilayas (expected 48+)")
        
        # 5. Authentication
        print("\n5️⃣ Authentication")
        admin_result = self.test_admin_login()
        
        # Additional verification tests
        print("\n🔍 Additional Verification Tests")
        
        # Verify category filtering works correctly
        if products_result and categories_result:
            # Find seeds category
            seeds_category = None
            for cat in categories_result:
                if cat.get('category_id') == 'cat_seeds':
                    seeds_category = cat
                    break
            
            if seeds_category:
                # Test that filtered products only contain seeds
                filtered_products = self.test_endpoint("Verify Seeds Filter", "GET", "products?category_id=cat_seeds", 200)
                if filtered_products:
                    all_seeds = all(p.get('category_id') == 'cat_seeds' for p in filtered_products)
                    if all_seeds:
                        self.log_test("Category Filter Verification", True, f"All {len(filtered_products)} products are from seeds category")
                    else:
                        non_seeds = [p for p in filtered_products if p.get('category_id') != 'cat_seeds']
                        self.log_test("Category Filter Verification", False, f"Found {len(non_seeds)} non-seed products in seeds filter")
        
        # Verify products on sale have discounts
        if sale_products:
            products_with_discounts = 0
            for product in sale_products:
                if product.get('discount_percent') or product.get('old_price'):
                    products_with_discounts += 1
            
            if products_with_discounts == len(sale_products):
                self.log_test("Sale Products Discount Verification", True, f"All {len(sale_products)} sale products have discounts")
            else:
                self.log_test("Sale Products Discount Verification", False, f"Only {products_with_discounts}/{len(sale_products)} sale products have discounts")
        
        # Print summary
        print("\n" + "=" * 60)
        print(f"📊 Test Summary: {self.tests_passed}/{self.tests_run} tests passed")
        
        if self.tests_passed == self.tests_run:
            print("🎉 All specific endpoint tests passed!")
            return 0
        else:
            print(f"⚠️  {self.tests_run - self.tests_passed} tests failed")
            return 1

def main():
    tester = SpecificEndpointTester()
    return tester.run_specific_tests()

if __name__ == "__main__":
    exit(main())