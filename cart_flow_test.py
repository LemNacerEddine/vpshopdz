#!/usr/bin/env python3
"""
Specific test script for AgroYousfi e-commerce cart flow and product gallery
Based on the review request requirements
"""

import requests
import json
from datetime import datetime

class CartFlowTester:
    def __init__(self):
        self.api_url = "https://agroyousfi-store.preview.emergentagent.com"
        self.session = requests.Session()
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
        return success

    def test_cart_flow_with_cookies(self):
        """Test cart flow using cookies to maintain session"""
        print("\n🛒 Testing Cart Flow with Cookies...")
        
        # Step 1: Add item to cart (with session cookies)
        add_data = {"product_id": "prod_wheat01", "quantity": 1}
        
        try:
            response = self.session.post(
                f"{self.api_url}/api/cart/add",
                json=add_data,
                headers={"Content-Type": "application/json"}
            )
            
            success = response.status_code == 200
            details = f"Status: {response.status_code}"
            if not success:
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:200]}"
            
            self.log_test("Add item to cart with cookies", success, details)
            
            if not success:
                return False
                
        except Exception as e:
            self.log_test("Add item to cart with cookies", False, f"Exception: {str(e)}")
            return False
        
        # Step 2: Get cart (should contain the item)
        try:
            response = self.session.get(f"{self.api_url}/api/cart")
            
            success = response.status_code == 200
            details = f"Status: {response.status_code}"
            
            if success:
                cart_data = response.json()
                items = cart_data.get('items', [])
                if items:
                    details += f" - Found {len(items)} items in cart"
                    # Check if our item is there
                    wheat_item = next((item for item in items if item['product_id'] == 'prod_wheat01'), None)
                    if wheat_item:
                        details += f" - Wheat item quantity: {wheat_item['quantity']}"
                    else:
                        success = False
                        details += " - Wheat item not found in cart"
                else:
                    success = False
                    details += " - Cart is empty"
            else:
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:200]}"
            
            self.log_test("Get cart with session cookies", success, details)
            return success
            
        except Exception as e:
            self.log_test("Get cart with session cookies", False, f"Exception: {str(e)}")
            return False

    def test_complete_order_flow(self):
        """Test complete order flow as guest"""
        print("\n📦 Testing Complete Order Flow...")
        
        # First ensure we have items in cart
        add_data = {"product_id": "prod_wheat01", "quantity": 1}
        
        try:
            # Add item to cart
            response = self.session.post(
                f"{self.api_url}/api/cart/add",
                json=add_data,
                headers={"Content-Type": "application/json"}
            )
            
            if response.status_code != 200:
                self.log_test("Add item for order", False, f"Failed to add item: {response.status_code}")
                return False
            
            self.log_test("Add item for order", True, "Item added successfully")
            
        except Exception as e:
            self.log_test("Add item for order", False, f"Exception: {str(e)}")
            return False
        
        # Create order as guest
        order_data = {
            "customer_name": "أحمد",
            "phone": "0555111222", 
            "wilaya": "16 - الجزائر",
            "address": "حي الياسمين",
            "notes": ""
        }
        
        try:
            response = self.session.post(
                f"{self.api_url}/api/orders",
                json=order_data,
                headers={"Content-Type": "application/json"}
            )
            
            success = response.status_code == 200
            details = f"Status: {response.status_code}"
            
            if success:
                order_result = response.json()
                order_id = order_result.get('order_id')
                total = order_result.get('total')
                details += f" - Order ID: {order_id}, Total: {total} DZD"
            else:
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:200]}"
            
            self.log_test("Create order as guest", success, details)
            return success
            
        except Exception as e:
            self.log_test("Create order as guest", False, f"Exception: {str(e)}")
            return False

    def test_product_gallery_data(self):
        """Test product gallery data structure"""
        print("\n🖼️ Testing Product Gallery Data...")
        
        try:
            response = requests.get(f"{self.api_url}/api/products/prod_wheat01")
            
            success = response.status_code == 200
            details = f"Status: {response.status_code}"
            
            if success:
                product = response.json()
                
                # Check images array
                images = product.get('images', [])
                if len(images) == 4:
                    details += f" - ✅ Images array has 4 items"
                else:
                    success = False
                    details += f" - ❌ Images array has {len(images)} items (expected 4)"
                
                # Check video field
                video = product.get('video')
                if video:
                    details += f" - ✅ Video field present: {video[:50]}..."
                else:
                    success = False
                    details += f" - ❌ Video field missing or empty"
                
                # Additional product info
                name_ar = product.get('name_ar', '')
                price = product.get('price', 0)
                details += f" - Product: {name_ar}, Price: {price} DZD"
                
            else:
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:200]}"
            
            self.log_test("Product gallery data structure", success, details)
            return success
            
        except Exception as e:
            self.log_test("Product gallery data structure", False, f"Exception: {str(e)}")
            return False

    def test_phone_registration_flow(self):
        """Test phone registration flow (verify it still works)"""
        print("\n📱 Testing Phone Registration Flow...")
        
        # Generate unique phone number
        test_phone = f"0555{datetime.now().strftime('%H%M%S')}"
        
        try:
            # Step 1: Send OTP
            response = requests.post(
                f"{self.api_url}/api/auth/phone/send-otp",
                json={"phone": test_phone},
                headers={"Content-Type": "application/json"}
            )
            
            if response.status_code != 200:
                self.log_test("Send phone OTP", False, f"Status: {response.status_code}")
                return False
            
            otp_result = response.json()
            demo_code = otp_result.get('demo_code')
            
            if not demo_code:
                self.log_test("Send phone OTP", False, "No demo_code in response")
                return False
            
            self.log_test("Send phone OTP", True, f"OTP sent, code: {demo_code}")
            
            # Step 2: Verify OTP
            response = requests.post(
                f"{self.api_url}/api/auth/phone/verify-otp",
                json={"phone": test_phone, "code": demo_code},
                headers={"Content-Type": "application/json"}
            )
            
            if response.status_code != 200:
                self.log_test("Verify phone OTP", False, f"Status: {response.status_code}")
                return False
            
            verify_result = response.json()
            status = verify_result.get('status')
            
            if status != 'new_user':
                self.log_test("Verify phone OTP", False, f"Expected 'new_user', got '{status}'")
                return False
            
            self.log_test("Verify phone OTP", True, f"Status: {status}")
            
            # Step 3: Complete registration
            register_data = {
                "phone": test_phone,
                "name": "أحمد محمد",
                "wilaya": "16 - الجزائر",
                "address": "شارع الاستقلال"
            }
            
            response = requests.post(
                f"{self.api_url}/api/auth/phone/register",
                json=register_data,
                headers={"Content-Type": "application/json"}
            )
            
            success = response.status_code == 200
            details = f"Status: {response.status_code}"
            
            if success:
                register_result = response.json()
                user = register_result.get('user', {})
                user_id = user.get('user_id')
                details += f" - User created: {user_id}"
            else:
                try:
                    error_data = response.json()
                    details += f" - {error_data.get('detail', 'Unknown error')}"
                except:
                    details += f" - {response.text[:200]}"
            
            self.log_test("Complete phone registration", success, details)
            return success
            
        except Exception as e:
            self.log_test("Phone registration flow", False, f"Exception: {str(e)}")
            return False

    def test_authenticated_features(self):
        """Test wishlist and addresses with authentication"""
        print("\n🔐 Testing Authenticated Features...")
        
        # First authenticate with phone
        test_phone = f"0555{datetime.now().strftime('%H%M%S')}"
        
        try:
            # Quick auth flow
            otp_response = requests.post(
                f"{self.api_url}/api/auth/phone/send-otp",
                json={"phone": test_phone}
            )
            
            if otp_response.status_code != 200:
                self.log_test("Auth for features test", False, "Failed to send OTP")
                return False
            
            demo_code = otp_response.json().get('demo_code')
            
            verify_response = requests.post(
                f"{self.api_url}/api/auth/phone/verify-otp",
                json={"phone": test_phone, "code": demo_code}
            )
            
            register_response = requests.post(
                f"{self.api_url}/api/auth/phone/register",
                json={
                    "phone": test_phone,
                    "name": "Test User",
                    "wilaya": "16 - الجزائر",
                    "address": "Test Address"
                }
            )
            
            if register_response.status_code != 200:
                self.log_test("Auth for features test", False, "Failed to register")
                return False
            
            session_token = register_response.json().get('session_token')
            headers = {"Authorization": f"Bearer {session_token}"}
            
            self.log_test("Authentication for features", True, "User authenticated")
            
            # Test wishlist
            wishlist_response = requests.get(
                f"{self.api_url}/api/wishlist",
                headers=headers
            )
            
            wishlist_success = wishlist_response.status_code == 200
            self.log_test("Get wishlist", wishlist_success, f"Status: {wishlist_response.status_code}")
            
            # Test addresses
            addresses_response = requests.get(
                f"{self.api_url}/api/addresses",
                headers=headers
            )
            
            addresses_success = addresses_response.status_code == 200
            self.log_test("Get addresses", addresses_success, f"Status: {addresses_response.status_code}")
            
            return wishlist_success and addresses_success
            
        except Exception as e:
            self.log_test("Authenticated features", False, f"Exception: {str(e)}")
            return False

    def run_all_tests(self):
        """Run all specific tests from review request"""
        print("🚀 Starting AgroYousfi Cart Flow & Gallery Tests...")
        print("=" * 60)
        
        # Test cart flow with cookies
        cart_success = self.test_cart_flow_with_cookies()
        
        # Test complete order flow
        order_success = self.test_complete_order_flow()
        
        # Test product gallery data
        gallery_success = self.test_product_gallery_data()
        
        # Test phone registration flow
        phone_success = self.test_phone_registration_flow()
        
        # Test authenticated features
        auth_success = self.test_authenticated_features()
        
        # Print summary
        print("\n" + "=" * 60)
        print(f"📊 Test Summary: {self.tests_passed}/{self.tests_run} tests passed")
        
        # Detailed results
        print("\n📋 Detailed Results:")
        print(f"   🛒 Cart Flow: {'✅ PASS' if cart_success else '❌ FAIL'}")
        print(f"   📦 Order Flow: {'✅ PASS' if order_success else '❌ FAIL'}")
        print(f"   🖼️ Gallery Data: {'✅ PASS' if gallery_success else '❌ FAIL'}")
        print(f"   📱 Phone Auth: {'✅ PASS' if phone_success else '❌ FAIL'}")
        print(f"   🔐 Auth Features: {'✅ PASS' if auth_success else '❌ FAIL'}")
        
        if self.tests_passed == self.tests_run:
            print("\n🎉 All tests passed!")
            return 0
        else:
            print(f"\n⚠️  {self.tests_run - self.tests_passed} tests failed")
            return 1

def main():
    tester = CartFlowTester()
    return tester.run_all_tests()

if __name__ == "__main__":
    exit(main())