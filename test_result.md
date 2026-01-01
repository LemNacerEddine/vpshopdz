#====================================================================================================
# START - Testing Protocol - DO NOT EDIT OR REMOVE THIS SECTION
#====================================================================================================

# THIS SECTION CONTAINS CRITICAL TESTING INSTRUCTIONS FOR BOTH AGENTS
# BOTH MAIN_AGENT AND TESTING_AGENT MUST PRESERVE THIS ENTIRE BLOCK

# Communication Protocol:
# If the `testing_agent` is available, main agent should delegate all testing tasks to it.
#
# You have access to a file called `test_result.md`. This file contains the complete testing state
# and history, and is the primary means of communication between main and the testing agent.
#
# Main and testing agents must follow this exact format to maintain testing data. 
# The testing data must be entered in yaml format Below is the data structure:
# 
## user_problem_statement: {problem_statement}
## backend:
##   - task: "Task name"
##     implemented: true
##     working: true  # or false or "NA"
##     file: "file_path.py"
##     stuck_count: 0
##     priority: "high"  # or "medium" or "low"
##     needs_retesting: false
##     status_history:
##         -working: true  # or false or "NA"
##         -agent: "main"  # or "testing" or "user"
##         -comment: "Detailed comment about status"
##
## frontend:
##   - task: "Task name"
##     implemented: true
##     working: true  # or false or "NA"
##     file: "file_path.js"
##     stuck_count: 0
##     priority: "high"  # or "medium" or "low"
##     needs_retesting: false
##     status_history:
##         -working: true  # or false or "NA"
##         -agent: "main"  # or "testing" or "user"
##         -comment: "Detailed comment about status"
##
## metadata:
##   created_by: "main_agent"
##   version: "1.0"
##   test_sequence: 0
##   run_ui: false
##
## test_plan:
##   current_focus:
##     - "Task name 1"
##     - "Task name 2"
##   stuck_tasks:
##     - "Task name with persistent issues"
##   test_all: false
##   test_priority: "high_first"  # or "sequential" or "stuck_first"
##
## agent_communication:
##     -agent: "main"  # or "testing" or "user"
##     -message: "Communication message between agents"

# Protocol Guidelines for Main agent
#
# 1. Update Test Result File Before Testing:
#    - Main agent must always update the `test_result.md` file before calling the testing agent
#    - Add implementation details to the status_history
#    - Set `needs_retesting` to true for tasks that need testing
#    - Update the `test_plan` section to guide testing
#
# 2. Incorporate User Feedback:
#    - When a user reports issues with a specific flow or page, add this information to the
#      `test_plan` section under `current_focus`
#    - Include specific reproduction steps if provided by the user
#    - Set priority to "high" for user-reported issues
#
# 3. Mark Tasks for Retesting:
#    - After fixing a bug or implementing a feature, set `needs_retesting: true`
#    - Add a comment in the `status_history` explaining what was changed
#
# 4. Communicate with Testing Agent:
#    - Use `agent_communication` to pass specific testing instructions
#    - Include context about recent changes that might affect testing
#    - Mention any known issues or limitations
#
# Protocol Guidelines for Testing Agent
#
# 1. Prioritize Testing Based on `test_plan`:
#    - Always check `current_focus` for priority tasks
#    - Test tasks with `needs_retesting: true` first
#    - For new tasks, ensure all acceptance criteria are met
#
# 2. Update Test Results Accurately:
#    - Set `working` to true only if the feature fully works
#    - Add detailed comments to `status_history` about what was tested
#    - Include screenshots or logs for any failures
#
# 3. Handle Stuck Tasks:
#    - If a task fails repeatedly, add it to `stuck_tasks`
#    - Provide detailed feedback about why it's stuck
#    - Suggest potential solutions in the comments
#
# 4. Communication with Main Agent:
#    - Use `agent_communication` to report findings
#    - Flag any questions referring to test_result.md
#
# IMPORTANT: Main agent must ALWAYS update test_result.md BEFORE calling the testing agent, as it relies on this file to understand what to test next.

#====================================================================================================
# END - Testing Protocol - DO NOT EDIT OR REMOVE THIS SECTION
#====================================================================================================



#====================================================================================================
# Testing Data - Main Agent and testing sub agent both should log testing data below this section
#====================================================================================================

user_problem_statement: "Build AgroYousfi e-commerce store with: multi-language support (AR/FR/EN), phone and Google registration, wishlist, saved addresses, functional search, reviews system, checkout with COD payment"

backend:
  - task: "Phone OTP Send API"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Implemented /api/auth/phone/send-otp - sends 6-digit OTP code"

  - task: "Phone OTP Verify API"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Implemented /api/auth/phone/verify-otp - returns new_user or existing_user status"

  - task: "Phone Registration API"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Implemented /api/auth/phone/register - creates user with phone, name, wilaya, address"

  - task: "Link Email API"
    implemented: true
    working: NA
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "medium"
    needs_retesting: true
    status_history:
      - working: NA
        agent: "main"
        comment: "Implemented /api/auth/link-email - allows phone users to link email"

  - task: "Wilayas API"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Implemented /api/wilayas - returns 58 Algerian wilayas"

  - task: "Product Search API"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Search working - tested with Arabic and English queries"

  - task: "Wishlist APIs"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "GET/POST/DELETE wishlist endpoints working"

  - task: "Addresses APIs"
    implemented: true
    working: true
    file: "/app/backend/server.py"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "GET/POST/PUT/DELETE addresses endpoints working"

frontend:
  - task: "Phone Login UI"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/LoginPage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Added tabs for Phone/Email login with OTP flow"

  - task: "Phone Registration Form"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/LoginPage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Registration form with name, wilaya dropdown, address fields"

  - task: "Product Search via Navbar"
    implemented: true
    working: true
    file: "/app/frontend/src/components/layout/Navbar.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Search navigates to /products?search=query"

  - task: "Products Page Search Filter"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/ProductsPage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Reads search param and filters products"

  - task: "Wishlist Button on Product Detail"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/ProductDetailPage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Added heart button to toggle wishlist"

  - task: "Profile Page Wishlist Tab"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/ProfilePage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Wishlist tab shows products with remove option"

  - task: "Profile Page Addresses Tab"
    implemented: true
    working: true
    file: "/app/frontend/src/pages/ProfilePage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: true
        agent: "main"
        comment: "Addresses tab with add/delete functionality"

  - task: "Link Email Feature"
    implemented: true
    working: NA
    file: "/app/frontend/src/pages/ProfilePage.jsx"
    stuck_count: 0
    priority: "medium"
    needs_retesting: true
    status_history:
      - working: NA
        agent: "main"
        comment: "Shows link email dialog for phone-registered users"

  - task: "Google Login"
    implemented: true
    working: NA
    file: "/app/frontend/src/pages/LoginPage.jsx"
    stuck_count: 0
    priority: "high"
    needs_retesting: true
    status_history:
      - working: NA
        agent: "main"
        comment: "Google OAuth button integrated with Emergent Auth"

metadata:
  created_by: "main_agent"
  version: "2.0"
  test_sequence: 1
  run_ui: true

test_plan:
  current_focus:
    - "Phone Login UI"
    - "Phone Registration Form"
    - "Product Search via Navbar"
    - "Wishlist Button on Product Detail"
    - "Profile Page Features (Orders, Wishlist, Addresses)"
    - "Google Login Flow"
  stuck_tasks: []
  test_all: true
  test_priority: "high_first"

agent_communication:
  - agent: "main"
    message: |
      New features implemented:
      1. Phone-based registration with OTP verification
      2. Registration form with name, wilaya (dropdown with 58 wilayas), address
      3. Link email feature for phone-registered users
      4. Search functionality - navbar search redirects to products page with search filter
      5. Wishlist button on product detail page
      6. Profile page enhancements (wishlist tab, addresses tab)
      
      Please test:
      - Full phone registration flow (send OTP -> verify -> register)
      - Search from navbar and products page
      - Add/remove from wishlist
      - Profile page tabs (orders, wishlist, addresses)
      - Google OAuth flow
      
      Test credentials:
      - Use any phone number for testing (demo code shown in response)
      - For existing features, use email OTP flow (demo code shown in response)
