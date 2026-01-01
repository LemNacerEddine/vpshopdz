from fastapi import FastAPI, APIRouter, HTTPException, Depends, Response, Request, Query
from fastapi.responses import JSONResponse
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field, ConfigDict, EmailStr
from typing import Union
from typing import List, Optional, Dict, Any
import uuid
from datetime import datetime, timezone, timedelta
import httpx
import random
import string

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

app = FastAPI(title="AgroYousfi API")
api_router = APIRouter(prefix="/api")

# ============ MODELS ============

class UserBase(BaseModel):
    model_config = ConfigDict(extra="ignore")
    email: Union[EmailStr, None] = None
    name: str
    phone: Optional[str] = None
    address: Optional[str] = None
    wilaya: Optional[str] = None
    role: str = "customer"  # customer, admin

class UserCreate(BaseModel):
    email: EmailStr
    name: str
    phone: Optional[str] = None

class User(UserBase):
    user_id: str
    picture: Optional[str] = None
    created_at: datetime

class CategoryBase(BaseModel):
    name_ar: str
    name_fr: str
    name_en: str
    description_ar: Optional[str] = None
    description_fr: Optional[str] = None
    description_en: Optional[str] = None
    image: Optional[str] = None
    icon: Optional[str] = None

class CategoryCreate(CategoryBase):
    pass

class Category(CategoryBase):
    model_config = ConfigDict(extra="ignore")
    category_id: str
    created_at: datetime

class ProductBase(BaseModel):
    name_ar: str
    name_fr: str
    name_en: str
    description_ar: Optional[str] = None
    description_fr: Optional[str] = None
    description_en: Optional[str] = None
    price: float
    old_price: Optional[float] = None
    stock: int = 0
    category_id: str
    images: List[str] = []
    video: Optional[str] = None  # Video URL for product demo
    featured: bool = False
    unit: str = "piece"  # piece, kg, pack

class ProductCreate(ProductBase):
    pass

class Product(ProductBase):
    model_config = ConfigDict(extra="ignore")
    product_id: str
    rating: float = 0.0
    reviews_count: int = 0
    created_at: datetime

class CartItem(BaseModel):
    product_id: str
    quantity: int

class CartUpdate(BaseModel):
    items: List[CartItem]

class Cart(BaseModel):
    model_config = ConfigDict(extra="ignore")
    cart_id: str
    user_id: str
    items: List[CartItem]
    updated_at: datetime

class OrderItem(BaseModel):
    product_id: str
    name: str
    price: float
    quantity: int

class OrderCreate(BaseModel):
    customer_name: str
    phone: str
    address: str
    wilaya: str
    notes: Optional[str] = None

class Order(BaseModel):
    model_config = ConfigDict(extra="ignore")
    order_id: str
    user_id: Optional[str] = None
    customer_name: str
    phone: str
    address: str
    wilaya: str
    notes: Optional[str] = None
    items: List[OrderItem]
    total: float
    status: str = "pending"  # pending, confirmed, shipped, delivered, cancelled
    created_at: datetime

class ReviewCreate(BaseModel):
    product_id: str
    rating: int = Field(ge=1, le=5)
    comment: Optional[str] = None

class Review(BaseModel):
    model_config = ConfigDict(extra="ignore")
    review_id: str
    product_id: str
    user_id: str
    user_name: str
    rating: int
    comment: Optional[str] = None
    created_at: datetime

class OTPRequest(BaseModel):
    email: EmailStr

class OTPVerify(BaseModel):
    email: EmailStr
    code: str

# ============ ALGERIAN WILAYAS ============

WILAYAS = [
    "01 - أدرار (Adrar)",
    "02 - الشلف (Chlef)",
    "03 - الأغواط (Laghouat)",
    "04 - أم البواقي (Oum El Bouaghi)",
    "05 - باتنة (Batna)",
    "06 - بجاية (Béjaïa)",
    "07 - بسكرة (Biskra)",
    "08 - بشار (Béchar)",
    "09 - البليدة (Blida)",
    "10 - البويرة (Bouira)",
    "11 - تمنراست (Tamanrasset)",
    "12 - تبسة (Tébessa)",
    "13 - تلمسان (Tlemcen)",
    "14 - تيارت (Tiaret)",
    "15 - تيزي وزو (Tizi Ouzou)",
    "16 - الجزائر (Alger)",
    "17 - الجلفة (Djelfa)",
    "18 - جيجل (Jijel)",
    "19 - سطيف (Sétif)",
    "20 - سعيدة (Saïda)",
    "21 - سكيكدة (Skikda)",
    "22 - سيدي بلعباس (Sidi Bel Abbès)",
    "23 - عنابة (Annaba)",
    "24 - قالمة (Guelma)",
    "25 - قسنطينة (Constantine)",
    "26 - المدية (Médéa)",
    "27 - مستغانم (Mostaganem)",
    "28 - المسيلة (M'Sila)",
    "29 - معسكر (Mascara)",
    "30 - ورقلة (Ouargla)",
    "31 - وهران (Oran)",
    "32 - البيض (El Bayadh)",
    "33 - إليزي (Illizi)",
    "34 - برج بوعريريج (Bordj Bou Arréridj)",
    "35 - بومرداس (Boumerdès)",
    "36 - الطارف (El Tarf)",
    "37 - تندوف (Tindouf)",
    "38 - تيسمسيلت (Tissemsilt)",
    "39 - الوادي (El Oued)",
    "40 - خنشلة (Khenchela)",
    "41 - سوق أهراس (Souk Ahras)",
    "42 - تيبازة (Tipaza)",
    "43 - ميلة (Mila)",
    "44 - عين الدفلى (Aïn Defla)",
    "45 - النعامة (Naâma)",
    "46 - عين تموشنت (Aïn Témouchent)",
    "47 - غرداية (Ghardaïa)",
    "48 - غليزان (Relizane)",
    "49 - تيميمون (Timimoun)",
    "50 - برج باجي مختار (Bordj Badji Mokhtar)",
    "51 - أولاد جلال (Ouled Djellal)",
    "52 - بني عباس (Béni Abbès)",
    "53 - عين صالح (In Salah)",
    "54 - عين قزام (In Guezzam)",
    "55 - توقرت (Touggourt)",
    "56 - جانت (Djanet)",
    "57 - المغير (El M'Ghair)",
    "58 - المنيعة (El Meniaa)"
]

# ============ NEW AUTH MODELS ============

class PhoneRegisterRequest(BaseModel):
    phone: str
    name: str
    wilaya: str
    address: Optional[str] = None

class PhoneOTPRequest(BaseModel):
    phone: str

class PhoneOTPVerify(BaseModel):
    phone: str
    code: str

class LinkEmailRequest(BaseModel):
    email: Union[EmailStr, str]

# ============ AUTH HELPERS ============

async def get_current_user(request: Request) -> Optional[User]:
    """Get current user from session token (cookie or header)"""
    session_token = request.cookies.get("session_token")
    if not session_token:
        auth_header = request.headers.get("Authorization")
        if auth_header and auth_header.startswith("Bearer "):
            session_token = auth_header.split(" ")[1]
    
    if not session_token:
        return None
    
    session = await db.user_sessions.find_one({"session_token": session_token}, {"_id": 0})
    if not session:
        return None
    
    expires_at = session.get("expires_at")
    if isinstance(expires_at, str):
        expires_at = datetime.fromisoformat(expires_at)
    if expires_at.tzinfo is None:
        expires_at = expires_at.replace(tzinfo=timezone.utc)
    if expires_at < datetime.now(timezone.utc):
        return None
    
    user = await db.users.find_one({"user_id": session["user_id"]}, {"_id": 0})
    if not user:
        return None
    
    if isinstance(user.get("created_at"), str):
        user["created_at"] = datetime.fromisoformat(user["created_at"])
    
    return User(**user)

async def require_auth(request: Request) -> User:
    """Require authentication"""
    user = await get_current_user(request)
    if not user:
        raise HTTPException(status_code=401, detail="Not authenticated")
    return user

async def require_admin(request: Request) -> User:
    """Require admin role"""
    user = await require_auth(request)
    if user.role != "admin":
        raise HTTPException(status_code=403, detail="Admin access required")
    return user

# ============ AUTH ENDPOINTS ============

@api_router.post("/auth/send-otp")
async def send_otp(data: OTPRequest):
    """Send OTP code to email (simulated - prints to console)"""
    code = ''.join(random.choices(string.digits, k=6))
    expires_at = datetime.now(timezone.utc) + timedelta(minutes=10)
    
    await db.otp_codes.update_one(
        {"email": data.email},
        {"$set": {
            "email": data.email,
            "code": code,
            "expires_at": expires_at.isoformat(),
            "created_at": datetime.now(timezone.utc).isoformat()
        }},
        upsert=True
    )
    
    # In production, send email here
    logging.info(f"OTP for {data.email}: {code}")
    
    return {"message": "OTP sent successfully", "demo_code": code}  # Remove demo_code in production

@api_router.post("/auth/verify-otp")
async def verify_otp(data: OTPVerify, response: Response):
    """Verify OTP and create session"""
    otp_doc = await db.otp_codes.find_one({"email": data.email}, {"_id": 0})
    
    if not otp_doc:
        raise HTTPException(status_code=400, detail="No OTP found for this email")
    
    if otp_doc["code"] != data.code:
        raise HTTPException(status_code=400, detail="Invalid OTP code")
    
    expires_at = datetime.fromisoformat(otp_doc["expires_at"])
    if expires_at.tzinfo is None:
        expires_at = expires_at.replace(tzinfo=timezone.utc)
    if expires_at < datetime.now(timezone.utc):
        raise HTTPException(status_code=400, detail="OTP expired")
    
    # Delete used OTP
    await db.otp_codes.delete_one({"email": data.email})
    
    # Find or create user
    user = await db.users.find_one({"email": data.email}, {"_id": 0})
    if not user:
        user_id = f"user_{uuid.uuid4().hex[:12]}"
        user = {
            "user_id": user_id,
            "email": data.email,
            "name": data.email.split("@")[0],
            "role": "customer",
            "created_at": datetime.now(timezone.utc).isoformat()
        }
        await db.users.insert_one(user)
    
    # Create session
    session_token = f"session_{uuid.uuid4().hex}"
    session_expires = datetime.now(timezone.utc) + timedelta(days=7)
    
    await db.user_sessions.insert_one({
        "user_id": user["user_id"],
        "session_token": session_token,
        "expires_at": session_expires.isoformat(),
        "created_at": datetime.now(timezone.utc).isoformat()
    })
    
    # Set cookie
    response.set_cookie(
        key="session_token",
        value=session_token,
        httponly=True,
        secure=True,
        samesite="none",
        max_age=7*24*60*60,
        path="/"
    )
    
    if isinstance(user.get("created_at"), str):
        user["created_at"] = datetime.fromisoformat(user["created_at"])
    
    return {"user": User(**user), "session_token": session_token}

@api_router.post("/auth/session")
async def process_google_session(request: Request, response: Response):
    """Process Google OAuth session_id and create local session"""
    body = await request.json()
    session_id = body.get("session_id")
    
    if not session_id:
        raise HTTPException(status_code=400, detail="session_id required")
    
    # Fetch user data from Emergent Auth
    async with httpx.AsyncClient() as client_http:
        resp = await client_http.get(
            "https://demobackend.emergentagent.com/auth/v1/env/oauth/session-data",
            headers={"X-Session-ID": session_id}
        )
        if resp.status_code != 200:
            raise HTTPException(status_code=401, detail="Invalid session")
        google_data = resp.json()
    
    # Find or create user
    user = await db.users.find_one({"email": google_data["email"]}, {"_id": 0})
    if not user:
        user_id = f"user_{uuid.uuid4().hex[:12]}"
        user = {
            "user_id": user_id,
            "email": google_data["email"],
            "name": google_data.get("name", ""),
            "picture": google_data.get("picture"),
            "role": "customer",
            "created_at": datetime.now(timezone.utc).isoformat()
        }
        await db.users.insert_one(user)
    else:
        # Update user info from Google
        await db.users.update_one(
            {"email": google_data["email"]},
            {"$set": {
                "name": google_data.get("name", user.get("name", "")),
                "picture": google_data.get("picture")
            }}
        )
        user = await db.users.find_one({"email": google_data["email"]}, {"_id": 0})
    
    # Create session
    session_token = f"session_{uuid.uuid4().hex}"
    session_expires = datetime.now(timezone.utc) + timedelta(days=7)
    
    await db.user_sessions.insert_one({
        "user_id": user["user_id"],
        "session_token": session_token,
        "expires_at": session_expires.isoformat(),
        "created_at": datetime.now(timezone.utc).isoformat()
    })
    
    # Set cookie
    response.set_cookie(
        key="session_token",
        value=session_token,
        httponly=True,
        secure=True,
        samesite="none",
        max_age=7*24*60*60,
        path="/"
    )
    
    if isinstance(user.get("created_at"), str):
        user["created_at"] = datetime.fromisoformat(user["created_at"])
    
    return {"user": User(**user), "session_token": session_token}

@api_router.get("/auth/me")
async def get_me(request: Request):
    """Get current authenticated user"""
    user = await get_current_user(request)
    if not user:
        raise HTTPException(status_code=401, detail="Not authenticated")
    return user

@api_router.post("/auth/logout")
async def logout(request: Request, response: Response):
    """Logout user"""
    session_token = request.cookies.get("session_token")
    if session_token:
        await db.user_sessions.delete_one({"session_token": session_token})
    
    response.delete_cookie(key="session_token", path="/")
    return {"message": "Logged out successfully"}

@api_router.put("/auth/profile")
async def update_profile(request: Request, user: User = Depends(require_auth)):
    """Update user profile"""
    body = await request.json()
    update_data = {}
    
    for field in ["name", "phone", "address", "wilaya"]:
        if field in body:
            update_data[field] = body[field]
    
    if update_data:
        await db.users.update_one(
            {"user_id": user.user_id},
            {"$set": update_data}
        )
    
    updated_user = await db.users.find_one({"user_id": user.user_id}, {"_id": 0})
    if isinstance(updated_user.get("created_at"), str):
        updated_user["created_at"] = datetime.fromisoformat(updated_user["created_at"])
    
    return User(**updated_user)

# ============ PHONE-BASED REGISTRATION ============

@api_router.post("/auth/phone/send-otp")
async def send_phone_otp(data: PhoneOTPRequest):
    """Send OTP code to phone (simulated - prints to console)"""
    code = ''.join(random.choices(string.digits, k=6))
    expires_at = datetime.now(timezone.utc) + timedelta(minutes=10)
    
    await db.phone_otp_codes.update_one(
        {"phone": data.phone},
        {"$set": {
            "phone": data.phone,
            "code": code,
            "expires_at": expires_at.isoformat(),
            "created_at": datetime.now(timezone.utc).isoformat()
        }},
        upsert=True
    )
    
    # In production, send SMS here
    logging.info(f"OTP for {data.phone}: {code}")
    
    return {"message": "OTP sent successfully", "demo_code": code}

@api_router.post("/auth/phone/verify-otp")
async def verify_phone_otp(data: PhoneOTPVerify, response: Response):
    """Verify phone OTP and login/register user"""
    otp_doc = await db.phone_otp_codes.find_one({"phone": data.phone}, {"_id": 0})
    
    if not otp_doc:
        raise HTTPException(status_code=400, detail="No OTP found for this phone")
    
    if otp_doc["code"] != data.code:
        raise HTTPException(status_code=400, detail="Invalid OTP code")
    
    expires_at = datetime.fromisoformat(otp_doc["expires_at"])
    if expires_at.tzinfo is None:
        expires_at = expires_at.replace(tzinfo=timezone.utc)
    if expires_at < datetime.now(timezone.utc):
        raise HTTPException(status_code=400, detail="OTP expired")
    
    # Delete used OTP
    await db.phone_otp_codes.delete_one({"phone": data.phone})
    
    # Find user by phone
    user = await db.users.find_one({"phone": data.phone}, {"_id": 0})
    
    if not user:
        # Return indicator that user needs to complete registration
        return {"status": "new_user", "phone": data.phone, "message": "Please complete registration"}
    
    # User exists, create session
    session_token = f"session_{uuid.uuid4().hex}"
    session_expires = datetime.now(timezone.utc) + timedelta(days=7)
    
    await db.user_sessions.insert_one({
        "user_id": user["user_id"],
        "session_token": session_token,
        "expires_at": session_expires.isoformat(),
        "created_at": datetime.now(timezone.utc).isoformat()
    })
    
    # Set cookie
    response.set_cookie(
        key="session_token",
        value=session_token,
        httponly=True,
        secure=True,
        samesite="none",
        max_age=7*24*60*60,
        path="/"
    )
    
    if isinstance(user.get("created_at"), str):
        user["created_at"] = datetime.fromisoformat(user["created_at"])
    
    return {"status": "existing_user", "user": User(**user), "session_token": session_token}

@api_router.post("/auth/phone/register")
async def register_with_phone(data: PhoneRegisterRequest, response: Response):
    """Complete registration with phone number"""
    # Check if phone already registered
    existing = await db.users.find_one({"phone": data.phone}, {"_id": 0})
    if existing:
        raise HTTPException(status_code=400, detail="Phone number already registered")
    
    # Create new user
    user_id = f"user_{uuid.uuid4().hex[:12]}"
    user = {
        "user_id": user_id,
        "phone": data.phone,
        "name": data.name,
        "wilaya": data.wilaya,
        "address": data.address or "",
        "email": None,
        "role": "customer",
        "created_at": datetime.now(timezone.utc).isoformat()
    }
    await db.users.insert_one(user)
    
    # Create session
    session_token = f"session_{uuid.uuid4().hex}"
    session_expires = datetime.now(timezone.utc) + timedelta(days=7)
    
    await db.user_sessions.insert_one({
        "user_id": user_id,
        "session_token": session_token,
        "expires_at": session_expires.isoformat(),
        "created_at": datetime.now(timezone.utc).isoformat()
    })
    
    # Set cookie
    response.set_cookie(
        key="session_token",
        value=session_token,
        httponly=True,
        secure=True,
        samesite="none",
        max_age=7*24*60*60,
        path="/"
    )
    
    user["created_at"] = datetime.fromisoformat(user["created_at"])
    
    return {"user": User(**user), "session_token": session_token}

@api_router.post("/auth/link-email")
async def link_email_to_account(data: LinkEmailRequest, user: User = Depends(require_auth)):
    """Link email to existing phone-based account"""
    # Check if email already used
    existing = await db.users.find_one({"email": data.email, "user_id": {"$ne": user.user_id}}, {"_id": 0})
    if existing:
        raise HTTPException(status_code=400, detail="Email already used by another account")
    
    await db.users.update_one(
        {"user_id": user.user_id},
        {"$set": {"email": data.email}}
    )
    
    updated_user = await db.users.find_one({"user_id": user.user_id}, {"_id": 0})
    if isinstance(updated_user.get("created_at"), str):
        updated_user["created_at"] = datetime.fromisoformat(updated_user["created_at"])
    
    return {"message": "Email linked successfully", "user": User(**updated_user)}

# ============ WILAYAS ENDPOINT ============

@api_router.get("/wilayas")
async def get_wilayas():
    """Get list of all Algerian wilayas"""
    return WILAYAS

# ============ CATEGORY ENDPOINTS ============

@api_router.get("/categories", response_model=List[Category])
async def get_categories():
    """Get all categories"""
    categories = await db.categories.find({}, {"_id": 0}).to_list(100)
    for cat in categories:
        if isinstance(cat.get("created_at"), str):
            cat["created_at"] = datetime.fromisoformat(cat["created_at"])
    return categories

@api_router.get("/categories/{category_id}", response_model=Category)
async def get_category(category_id: str):
    """Get single category"""
    category = await db.categories.find_one({"category_id": category_id}, {"_id": 0})
    if not category:
        raise HTTPException(status_code=404, detail="Category not found")
    if isinstance(category.get("created_at"), str):
        category["created_at"] = datetime.fromisoformat(category["created_at"])
    return category

@api_router.post("/categories", response_model=Category)
async def create_category(data: CategoryCreate, user: User = Depends(require_admin)):
    """Create category (admin only)"""
    category = Category(
        category_id=f"cat_{uuid.uuid4().hex[:8]}",
        created_at=datetime.now(timezone.utc),
        **data.model_dump()
    )
    doc = category.model_dump()
    doc["created_at"] = doc["created_at"].isoformat()
    await db.categories.insert_one(doc)
    return category

@api_router.put("/categories/{category_id}", response_model=Category)
async def update_category(category_id: str, data: CategoryCreate, user: User = Depends(require_admin)):
    """Update category (admin only)"""
    result = await db.categories.update_one(
        {"category_id": category_id},
        {"$set": data.model_dump()}
    )
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Category not found")
    
    category = await db.categories.find_one({"category_id": category_id}, {"_id": 0})
    if isinstance(category.get("created_at"), str):
        category["created_at"] = datetime.fromisoformat(category["created_at"])
    return category

@api_router.delete("/categories/{category_id}")
async def delete_category(category_id: str, user: User = Depends(require_admin)):
    """Delete category (admin only)"""
    result = await db.categories.delete_one({"category_id": category_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Category not found")
    return {"message": "Category deleted"}

# ============ PRODUCT ENDPOINTS ============

@api_router.get("/products", response_model=List[Product])
async def get_products(
    category_id: Optional[str] = None,
    featured: Optional[bool] = None,
    search: Optional[str] = None,
    min_price: Optional[float] = None,
    max_price: Optional[float] = None,
    limit: int = Query(default=50, le=100),
    skip: int = 0
):
    """Get products with filters"""
    query: Dict[str, Any] = {}
    
    if category_id:
        query["category_id"] = category_id
    if featured is not None:
        query["featured"] = featured
    if min_price is not None:
        query["price"] = {"$gte": min_price}
    if max_price is not None:
        query["price"] = {**query.get("price", {}), "$lte": max_price}
    if search:
        query["$or"] = [
            {"name_ar": {"$regex": search, "$options": "i"}},
            {"name_fr": {"$regex": search, "$options": "i"}},
            {"name_en": {"$regex": search, "$options": "i"}}
        ]
    
    products = await db.products.find(query, {"_id": 0}).skip(skip).limit(limit).to_list(limit)
    for prod in products:
        if isinstance(prod.get("created_at"), str):
            prod["created_at"] = datetime.fromisoformat(prod["created_at"])
    return products

@api_router.get("/products/{product_id}", response_model=Product)
async def get_product(product_id: str):
    """Get single product"""
    product = await db.products.find_one({"product_id": product_id}, {"_id": 0})
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
    if isinstance(product.get("created_at"), str):
        product["created_at"] = datetime.fromisoformat(product["created_at"])
    return product

@api_router.post("/products", response_model=Product)
async def create_product(data: ProductCreate, user: User = Depends(require_admin)):
    """Create product (admin only)"""
    product = Product(
        product_id=f"prod_{uuid.uuid4().hex[:8]}",
        created_at=datetime.now(timezone.utc),
        **data.model_dump()
    )
    doc = product.model_dump()
    doc["created_at"] = doc["created_at"].isoformat()
    await db.products.insert_one(doc)
    return product

@api_router.put("/products/{product_id}", response_model=Product)
async def update_product(product_id: str, data: ProductCreate, user: User = Depends(require_admin)):
    """Update product (admin only)"""
    result = await db.products.update_one(
        {"product_id": product_id},
        {"$set": data.model_dump()}
    )
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
    
    product = await db.products.find_one({"product_id": product_id}, {"_id": 0})
    if isinstance(product.get("created_at"), str):
        product["created_at"] = datetime.fromisoformat(product["created_at"])
    return product

@api_router.delete("/products/{product_id}")
async def delete_product(product_id: str, user: User = Depends(require_admin)):
    """Delete product (admin only)"""
    result = await db.products.delete_one({"product_id": product_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
    return {"message": "Product deleted"}

# ============ CART ENDPOINTS ============

@api_router.get("/cart")
async def get_cart(request: Request):
    """Get user cart"""
    user = await get_current_user(request)
    user_id = user.user_id if user else request.cookies.get("guest_cart_id", f"guest_{uuid.uuid4().hex[:8]}")
    
    cart = await db.carts.find_one({"user_id": user_id}, {"_id": 0})
    if not cart:
        cart = {"cart_id": f"cart_{uuid.uuid4().hex[:8]}", "user_id": user_id, "items": [], "updated_at": datetime.now(timezone.utc).isoformat()}
    
    # Fetch product details for cart items
    items_with_details = []
    for item in cart.get("items", []):
        product = await db.products.find_one({"product_id": item["product_id"]}, {"_id": 0})
        if product:
            items_with_details.append({
                **item,
                "product": product
            })
    
    return {"cart_id": cart["cart_id"], "items": items_with_details}

@api_router.post("/cart/add")
async def add_to_cart(item: CartItem, request: Request, response: Response):
    """Add item to cart"""
    user = await get_current_user(request)
    
    if user:
        user_id = user.user_id
    else:
        user_id = request.cookies.get("guest_cart_id")
        if not user_id:
            user_id = f"guest_{uuid.uuid4().hex[:8]}"
            response.set_cookie(key="guest_cart_id", value=user_id, max_age=30*24*60*60, path="/")
    
    # Check product exists
    product = await db.products.find_one({"product_id": item.product_id}, {"_id": 0})
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
    
    cart = await db.carts.find_one({"user_id": user_id}, {"_id": 0})
    
    if not cart:
        cart = {
            "cart_id": f"cart_{uuid.uuid4().hex[:8]}",
            "user_id": user_id,
            "items": [],
            "updated_at": datetime.now(timezone.utc).isoformat()
        }
    
    # Update or add item
    found = False
    for cart_item in cart["items"]:
        if cart_item["product_id"] == item.product_id:
            cart_item["quantity"] += item.quantity
            found = True
            break
    
    if not found:
        cart["items"].append({"product_id": item.product_id, "quantity": item.quantity})
    
    cart["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    await db.carts.update_one(
        {"user_id": user_id},
        {"$set": cart},
        upsert=True
    )
    
    return {"message": "Item added to cart"}

@api_router.put("/cart/update")
async def update_cart(item: CartItem, request: Request):
    """Update cart item quantity"""
    user = await get_current_user(request)
    user_id = user.user_id if user else request.cookies.get("guest_cart_id")
    
    if not user_id:
        raise HTTPException(status_code=400, detail="No cart found")
    
    cart = await db.carts.find_one({"user_id": user_id}, {"_id": 0})
    if not cart:
        raise HTTPException(status_code=404, detail="Cart not found")
    
    if item.quantity <= 0:
        # Remove item
        cart["items"] = [i for i in cart["items"] if i["product_id"] != item.product_id]
    else:
        # Update quantity
        for cart_item in cart["items"]:
            if cart_item["product_id"] == item.product_id:
                cart_item["quantity"] = item.quantity
                break
    
    cart["updated_at"] = datetime.now(timezone.utc).isoformat()
    await db.carts.update_one({"user_id": user_id}, {"$set": cart})
    
    return {"message": "Cart updated"}

@api_router.delete("/cart/remove/{product_id}")
async def remove_from_cart(product_id: str, request: Request):
    """Remove item from cart"""
    user = await get_current_user(request)
    user_id = user.user_id if user else request.cookies.get("guest_cart_id")
    
    if not user_id:
        raise HTTPException(status_code=400, detail="No cart found")
    
    await db.carts.update_one(
        {"user_id": user_id},
        {"$pull": {"items": {"product_id": product_id}}}
    )
    
    return {"message": "Item removed from cart"}

@api_router.delete("/cart/clear")
async def clear_cart(request: Request):
    """Clear entire cart"""
    user = await get_current_user(request)
    user_id = user.user_id if user else request.cookies.get("guest_cart_id")
    
    if user_id:
        await db.carts.update_one({"user_id": user_id}, {"$set": {"items": []}})
    
    return {"message": "Cart cleared"}

# ============ ORDER ENDPOINTS ============

@api_router.post("/orders", response_model=Order)
async def create_order(data: OrderCreate, request: Request):
    """Create new order"""
    user = await get_current_user(request)
    user_id = user.user_id if user else request.cookies.get("guest_cart_id")
    
    # Get cart
    cart = await db.carts.find_one({"user_id": user_id}, {"_id": 0})
    if not cart or not cart.get("items"):
        raise HTTPException(status_code=400, detail="Cart is empty")
    
    # Build order items with product details
    order_items = []
    total = 0
    
    for item in cart["items"]:
        product = await db.products.find_one({"product_id": item["product_id"]}, {"_id": 0})
        if product:
            order_item = OrderItem(
                product_id=item["product_id"],
                name=product["name_ar"],
                price=product["price"],
                quantity=item["quantity"]
            )
            order_items.append(order_item)
            total += product["price"] * item["quantity"]
    
    order = Order(
        order_id=f"order_{uuid.uuid4().hex[:8]}",
        user_id=user.user_id if user else None,
        customer_name=data.customer_name,
        phone=data.phone,
        address=data.address,
        wilaya=data.wilaya,
        notes=data.notes,
        items=[item.model_dump() for item in order_items],
        total=total,
        status="pending",
        created_at=datetime.now(timezone.utc)
    )
    
    doc = order.model_dump()
    doc["created_at"] = doc["created_at"].isoformat()
    await db.orders.insert_one(doc)
    
    # Clear cart
    await db.carts.update_one({"user_id": user_id}, {"$set": {"items": []}})
    
    return order

@api_router.get("/orders", response_model=List[Order])
async def get_orders(request: Request, user: User = Depends(require_auth)):
    """Get user orders"""
    orders = await db.orders.find({"user_id": user.user_id}, {"_id": 0}).sort("created_at", -1).to_list(100)
    for order in orders:
        if isinstance(order.get("created_at"), str):
            order["created_at"] = datetime.fromisoformat(order["created_at"])
    return orders

@api_router.get("/orders/{order_id}", response_model=Order)
async def get_order(order_id: str, request: Request):
    """Get single order"""
    user = await get_current_user(request)
    
    query = {"order_id": order_id}
    if user and user.role != "admin":
        query["user_id"] = user.user_id
    
    order = await db.orders.find_one(query, {"_id": 0})
    if not order:
        raise HTTPException(status_code=404, detail="Order not found")
    
    if isinstance(order.get("created_at"), str):
        order["created_at"] = datetime.fromisoformat(order["created_at"])
    return order

# ============ ADMIN ORDER ENDPOINTS ============

@api_router.get("/admin/orders", response_model=List[Order])
async def admin_get_orders(
    status: Optional[str] = None,
    user: User = Depends(require_admin)
):
    """Get all orders (admin only)"""
    query = {}
    if status:
        query["status"] = status
    
    orders = await db.orders.find(query, {"_id": 0}).sort("created_at", -1).to_list(500)
    for order in orders:
        if isinstance(order.get("created_at"), str):
            order["created_at"] = datetime.fromisoformat(order["created_at"])
    return orders

@api_router.put("/admin/orders/{order_id}/status")
async def update_order_status(order_id: str, request: Request, user: User = Depends(require_admin)):
    """Update order status (admin only)"""
    body = await request.json()
    new_status = body.get("status")
    
    if new_status not in ["pending", "confirmed", "shipped", "delivered", "cancelled"]:
        raise HTTPException(status_code=400, detail="Invalid status")
    
    result = await db.orders.update_one(
        {"order_id": order_id},
        {"$set": {"status": new_status}}
    )
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Order not found")
    
    return {"message": "Order status updated"}

# ============ REVIEW ENDPOINTS ============

@api_router.get("/reviews/{product_id}", response_model=List[Review])
async def get_reviews(product_id: str):
    """Get product reviews"""
    reviews = await db.reviews.find({"product_id": product_id}, {"_id": 0}).sort("created_at", -1).to_list(100)
    for review in reviews:
        if isinstance(review.get("created_at"), str):
            review["created_at"] = datetime.fromisoformat(review["created_at"])
    return reviews

@api_router.post("/reviews", response_model=Review)
async def create_review(data: ReviewCreate, user: User = Depends(require_auth)):
    """Create product review"""
    # Check if user already reviewed this product
    existing = await db.reviews.find_one({
        "product_id": data.product_id,
        "user_id": user.user_id
    })
    if existing:
        raise HTTPException(status_code=400, detail="You already reviewed this product")
    
    review = Review(
        review_id=f"rev_{uuid.uuid4().hex[:8]}",
        product_id=data.product_id,
        user_id=user.user_id,
        user_name=user.name,
        rating=data.rating,
        comment=data.comment,
        created_at=datetime.now(timezone.utc)
    )
    
    doc = review.model_dump()
    doc["created_at"] = doc["created_at"].isoformat()
    await db.reviews.insert_one(doc)
    
    # Update product rating
    all_reviews = await db.reviews.find({"product_id": data.product_id}, {"_id": 0}).to_list(1000)
    avg_rating = sum(r["rating"] for r in all_reviews) / len(all_reviews)
    await db.products.update_one(
        {"product_id": data.product_id},
        {"$set": {"rating": round(avg_rating, 1), "reviews_count": len(all_reviews)}}
    )
    
    return review

# ============ ADMIN STATS ============

@api_router.get("/admin/stats")
async def get_admin_stats(user: User = Depends(require_admin)):
    """Get admin dashboard statistics"""
    total_products = await db.products.count_documents({})
    total_orders = await db.orders.count_documents({})
    pending_orders = await db.orders.count_documents({"status": "pending"})
    total_users = await db.users.count_documents({})
    
    # Calculate total revenue
    orders = await db.orders.find({"status": {"$in": ["confirmed", "shipped", "delivered"]}}, {"_id": 0, "total": 1}).to_list(10000)
    total_revenue = sum(o.get("total", 0) for o in orders)
    
    return {
        "total_products": total_products,
        "total_orders": total_orders,
        "pending_orders": pending_orders,
        "total_users": total_users,
        "total_revenue": total_revenue
    }

# ============ WISHLIST ENDPOINTS ============

@api_router.get("/wishlist")
async def get_wishlist(user: User = Depends(require_auth)):
    """Get user wishlist with product details"""
    wishlist_items = await db.wishlist.find({"user_id": user.user_id}, {"_id": 0}).to_list(100)
    
    # Fetch product details for each item
    for item in wishlist_items:
        product = await db.products.find_one({"product_id": item["product_id"]}, {"_id": 0})
        if product:
            if isinstance(product.get("created_at"), str):
                product["created_at"] = datetime.fromisoformat(product["created_at"])
            item["product"] = product
    
    return wishlist_items

@api_router.post("/wishlist/{product_id}")
async def add_to_wishlist(product_id: str, user: User = Depends(require_auth)):
    """Add product to wishlist"""
    # Check if product exists
    product = await db.products.find_one({"product_id": product_id})
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
    
    # Check if already in wishlist
    existing = await db.wishlist.find_one({"user_id": user.user_id, "product_id": product_id})
    if existing:
        return {"message": "Already in wishlist"}
    
    await db.wishlist.insert_one({
        "user_id": user.user_id,
        "product_id": product_id,
        "created_at": datetime.now(timezone.utc).isoformat()
    })
    
    return {"message": "Added to wishlist"}

@api_router.delete("/wishlist/{product_id}")
async def remove_from_wishlist(product_id: str, user: User = Depends(require_auth)):
    """Remove product from wishlist"""
    result = await db.wishlist.delete_one({"user_id": user.user_id, "product_id": product_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Item not in wishlist")
    return {"message": "Removed from wishlist"}

# ============ ADDRESS ENDPOINTS ============

@api_router.get("/addresses")
async def get_addresses(user: User = Depends(require_auth)):
    """Get user saved addresses"""
    addresses = await db.addresses.find({"user_id": user.user_id}, {"_id": 0}).to_list(20)
    return addresses

@api_router.post("/addresses")
async def add_address(request: Request, user: User = Depends(require_auth)):
    """Add new address"""
    body = await request.json()
    
    address_id = f"addr_{uuid.uuid4().hex[:8]}"
    address = {
        "address_id": address_id,
        "user_id": user.user_id,
        "title": body.get("title", ""),
        "phone": body.get("phone", ""),
        "address": body.get("address", ""),
        "wilaya": body.get("wilaya", ""),
        "isDefault": body.get("isDefault", False),
        "created_at": datetime.now(timezone.utc).isoformat()
    }
    
    # If this is set as default, unset other defaults
    if address["isDefault"]:
        await db.addresses.update_many(
            {"user_id": user.user_id},
            {"$set": {"isDefault": False}}
        )
    
    await db.addresses.insert_one(address)
    
    return {"message": "Address added", "address_id": address_id}

@api_router.put("/addresses/{address_id}")
async def update_address(address_id: str, request: Request, user: User = Depends(require_auth)):
    """Update address"""
    body = await request.json()
    
    update_data = {}
    for field in ["title", "phone", "address", "wilaya", "isDefault"]:
        if field in body:
            update_data[field] = body[field]
    
    # If setting as default, unset other defaults
    if update_data.get("isDefault"):
        await db.addresses.update_many(
            {"user_id": user.user_id, "address_id": {"$ne": address_id}},
            {"$set": {"isDefault": False}}
        )
    
    result = await db.addresses.update_one(
        {"address_id": address_id, "user_id": user.user_id},
        {"$set": update_data}
    )
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Address not found")
    
    return {"message": "Address updated"}

@api_router.delete("/addresses/{address_id}")
async def delete_address(address_id: str, user: User = Depends(require_auth)):
    """Delete address"""
    result = await db.addresses.delete_one({"address_id": address_id, "user_id": user.user_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Address not found")
    return {"message": "Address deleted"}

# ============ SEED DATA ============

@api_router.post("/seed")
async def seed_database():
    """Seed database with sample data"""
    # Check if already seeded
    existing = await db.categories.find_one({})
    if existing:
        return {"message": "Database already seeded"}
    
    # Categories
    categories = [
        {
            "category_id": "cat_seeds",
            "name_ar": "البذور",
            "name_fr": "Semences",
            "name_en": "Seeds",
            "description_ar": "بذور عالية الجودة لجميع أنواع المحاصيل",
            "description_fr": "Semences de haute qualité pour toutes les cultures",
            "description_en": "High quality seeds for all types of crops",
            "image": "https://images.pexels.com/photos/2290074/pexels-photo-2290074.jpeg",
            "icon": "Leaf",
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "category_id": "cat_fertilizers",
            "name_ar": "الأسمدة",
            "name_fr": "Engrais",
            "name_en": "Fertilizers",
            "description_ar": "أسمدة عضوية وكيميائية لتحسين التربة",
            "description_fr": "Engrais organiques et chimiques pour améliorer le sol",
            "description_en": "Organic and chemical fertilizers to improve soil",
            "image": "https://images.pexels.com/photos/5529765/pexels-photo-5529765.jpeg",
            "icon": "Droplets",
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "category_id": "cat_tools",
            "name_ar": "أدوات الزراعة",
            "name_fr": "Outils Agricoles",
            "name_en": "Farm Tools",
            "description_ar": "أدوات ومعدات زراعية متنوعة",
            "description_fr": "Outils et équipements agricoles divers",
            "description_en": "Various farming tools and equipment",
            "image": "https://images.pexels.com/photos/4856725/pexels-photo-4856725.jpeg",
            "icon": "Wrench",
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "category_id": "cat_pesticides",
            "name_ar": "المبيدات",
            "name_fr": "Pesticides",
            "name_en": "Pesticides",
            "description_ar": "مبيدات آمنة وفعالة لحماية المحاصيل",
            "description_fr": "Pesticides sûrs et efficaces pour protéger les cultures",
            "description_en": "Safe and effective pesticides to protect crops",
            "image": "https://images.pexels.com/photos/7457521/pexels-photo-7457521.jpeg",
            "icon": "Shield",
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "category_id": "cat_irrigation",
            "name_ar": "أنظمة الري",
            "name_fr": "Systèmes d'Irrigation",
            "name_en": "Irrigation Systems",
            "description_ar": "أنظمة ري حديثة وموفرة للمياه",
            "description_fr": "Systèmes d'irrigation modernes et économes en eau",
            "description_en": "Modern water-efficient irrigation systems",
            "image": "https://images.pexels.com/photos/12495821/pexels-photo-12495821.jpeg",
            "icon": "Droplet",
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "category_id": "cat_greenhouses",
            "name_ar": "البيوت البلاستيكية",
            "name_fr": "Serres",
            "name_en": "Greenhouses",
            "description_ar": "معدات ومستلزمات البيوت البلاستيكية",
            "description_fr": "Équipements et fournitures pour serres",
            "description_en": "Greenhouse equipment and supplies",
            "image": "https://images.pexels.com/photos/176169/pexels-photo-176169.jpeg",
            "icon": "Home",
            "created_at": datetime.now(timezone.utc).isoformat()
        }
    ]
    
    await db.categories.insert_many(categories)
    
    # Products
    products = [
        # Seeds
        {
            "product_id": "prod_wheat01",
            "name_ar": "بذور القمح الصلب",
            "name_fr": "Semences de Blé Dur",
            "name_en": "Durum Wheat Seeds",
            "description_ar": "بذور قمح صلب عالية الجودة، مناسبة للمناخ الجزائري. إنتاجية عالية ومقاومة للأمراض.",
            "description_fr": "Semences de blé dur de haute qualité, adaptées au climat algérien. Rendement élevé et résistance aux maladies.",
            "description_en": "High quality durum wheat seeds, suitable for Algerian climate. High yield and disease resistant.",
            "price": 4500,
            "old_price": 5000,
            "stock": 150,
            "category_id": "cat_seeds",
            "images": [
                "https://images.pexels.com/photos/6041/nature-grain-moving-cereal.jpg",
                "https://images.pexels.com/photos/326082/pexels-photo-326082.jpeg",
                "https://images.pexels.com/photos/2589457/pexels-photo-2589457.jpeg",
                "https://images.pexels.com/photos/1537169/pexels-photo-1537169.jpeg"
            ],
            "video": "https://videos.pexels.com/video-files/2795398/2795398-uhd_2560_1440_25fps.mp4",
            "featured": True,
            "unit": "kg",
            "rating": 4.8,
            "reviews_count": 24,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "product_id": "prod_tomato01",
            "name_ar": "بذور الطماطم الهجينة",
            "name_fr": "Semences de Tomates Hybrides",
            "name_en": "Hybrid Tomato Seeds",
            "description_ar": "بذور طماطم هجينة عالية الإنتاجية، مقاومة للحرارة والأمراض الشائعة.",
            "description_fr": "Semences de tomates hybrides à haut rendement, résistantes à la chaleur et aux maladies courantes.",
            "description_en": "High yield hybrid tomato seeds, resistant to heat and common diseases.",
            "price": 1800,
            "stock": 200,
            "category_id": "cat_seeds",
            "images": [
                "https://images.pexels.com/photos/2290074/pexels-photo-2290074.jpeg",
                "https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg",
                "https://images.pexels.com/photos/533280/pexels-photo-533280.jpeg",
                "https://images.pexels.com/photos/96616/pexels-photo-96616.jpeg"
            ],
            "featured": True,
            "unit": "pack",
            "rating": 4.5,
            "reviews_count": 18,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "product_id": "prod_pepper01",
            "name_ar": "بذور الفلفل الحلو",
            "name_fr": "Semences de Poivron Doux",
            "name_en": "Sweet Pepper Seeds",
            "description_ar": "بذور فلفل حلو متعددة الألوان، إنتاجية عالية وطعم مميز.",
            "description_fr": "Semences de poivron doux multicolore, rendement élevé et goût distinctif.",
            "description_en": "Multicolor sweet pepper seeds, high yield and distinctive taste.",
            "price": 1500,
            "stock": 180,
            "category_id": "cat_seeds",
            "images": [
                "https://images.pexels.com/photos/594137/pexels-photo-594137.jpeg",
                "https://images.pexels.com/photos/128536/pexels-photo-128536.jpeg",
                "https://images.pexels.com/photos/2893635/pexels-photo-2893635.jpeg"
            ],
            "featured": False,
            "unit": "pack",
            "rating": 4.3,
            "reviews_count": 12,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        # Fertilizers
        {
            "product_id": "prod_fert01",
            "name_ar": "سماد NPK 20-20-20",
            "name_fr": "Engrais NPK 20-20-20",
            "name_en": "NPK Fertilizer 20-20-20",
            "description_ar": "سماد متوازن NPK لجميع أنواع المحاصيل. يعزز النمو والإزهار والإثمار.",
            "description_fr": "Engrais NPK équilibré pour tous les types de cultures. Favorise la croissance, la floraison et la fructification.",
            "description_en": "Balanced NPK fertilizer for all crop types. Promotes growth, flowering and fruiting.",
            "price": 3200,
            "old_price": 3800,
            "stock": 100,
            "category_id": "cat_fertilizers",
            "images": ["https://images.pexels.com/photos/5529765/pexels-photo-5529765.jpeg"],
            "featured": True,
            "unit": "kg",
            "rating": 4.7,
            "reviews_count": 32,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "product_id": "prod_fert02",
            "name_ar": "سماد عضوي طبيعي",
            "name_fr": "Engrais Organique Naturel",
            "name_en": "Natural Organic Fertilizer",
            "description_ar": "سماد عضوي 100% طبيعي من مصادر حيوانية ونباتية. آمن للبيئة.",
            "description_fr": "Engrais 100% organique naturel de sources animales et végétales. Respectueux de l'environnement.",
            "description_en": "100% natural organic fertilizer from animal and plant sources. Environmentally friendly.",
            "price": 2800,
            "stock": 80,
            "category_id": "cat_fertilizers",
            "images": ["https://images.pexels.com/photos/7728082/pexels-photo-7728082.jpeg"],
            "featured": False,
            "unit": "kg",
            "rating": 4.6,
            "reviews_count": 15,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        # Tools
        {
            "product_id": "prod_tool01",
            "name_ar": "مجرفة يدوية احترافية",
            "name_fr": "Bêche Manuelle Professionnelle",
            "name_en": "Professional Hand Shovel",
            "description_ar": "مجرفة يدوية بمقبض خشبي متين. مثالية للحفر والزراعة.",
            "description_fr": "Bêche manuelle avec manche en bois robuste. Idéale pour creuser et planter.",
            "description_en": "Hand shovel with sturdy wooden handle. Ideal for digging and planting.",
            "price": 2500,
            "stock": 50,
            "category_id": "cat_tools",
            "images": ["https://images.pexels.com/photos/4856725/pexels-photo-4856725.jpeg"],
            "featured": True,
            "unit": "piece",
            "rating": 4.4,
            "reviews_count": 20,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        {
            "product_id": "prod_tool02",
            "name_ar": "مقص تقليم الأشجار",
            "name_fr": "Sécateur d'Arbres",
            "name_en": "Tree Pruning Shears",
            "description_ar": "مقص تقليم احترافي بشفرات فولاذية حادة. سهل الاستخدام ومتين.",
            "description_fr": "Sécateur professionnel avec lames en acier tranchantes. Facile à utiliser et durable.",
            "description_en": "Professional pruning shears with sharp steel blades. Easy to use and durable.",
            "price": 3500,
            "stock": 35,
            "category_id": "cat_tools",
            "images": ["https://images.pexels.com/photos/12495821/pexels-photo-12495821.jpeg"],
            "featured": False,
            "unit": "piece",
            "rating": 4.2,
            "reviews_count": 8,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        # Pesticides
        {
            "product_id": "prod_pest01",
            "name_ar": "مبيد حشري طبيعي",
            "name_fr": "Insecticide Naturel",
            "name_en": "Natural Insecticide",
            "description_ar": "مبيد حشري طبيعي وآمن للبيئة. فعال ضد الحشرات الضارة.",
            "description_fr": "Insecticide naturel et respectueux de l'environnement. Efficace contre les insectes nuisibles.",
            "description_en": "Natural and eco-friendly insecticide. Effective against harmful insects.",
            "price": 4200,
            "stock": 60,
            "category_id": "cat_pesticides",
            "images": ["https://images.pexels.com/photos/7457521/pexels-photo-7457521.jpeg"],
            "featured": True,
            "unit": "liter",
            "rating": 4.5,
            "reviews_count": 14,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        # Irrigation
        {
            "product_id": "prod_irrig01",
            "name_ar": "نظام ري بالتنقيط",
            "name_fr": "Système de Goutte à Goutte",
            "name_en": "Drip Irrigation System",
            "description_ar": "نظام ري بالتنقيط كامل. يوفر 60% من المياه مقارنة بالري التقليدي.",
            "description_fr": "Système d'irrigation goutte à goutte complet. Économise 60% d'eau par rapport à l'irrigation traditionnelle.",
            "description_en": "Complete drip irrigation system. Saves 60% water compared to traditional irrigation.",
            "price": 15000,
            "old_price": 18000,
            "stock": 25,
            "category_id": "cat_irrigation",
            "images": ["https://images.pexels.com/photos/12495821/pexels-photo-12495821.jpeg"],
            "featured": True,
            "unit": "kit",
            "rating": 4.9,
            "reviews_count": 28,
            "created_at": datetime.now(timezone.utc).isoformat()
        },
        # Greenhouses
        {
            "product_id": "prod_green01",
            "name_ar": "بلاستيك بيوت زراعية",
            "name_fr": "Plastique pour Serres",
            "name_en": "Greenhouse Plastic Sheet",
            "description_ar": "بلاستيك عالي الجودة للبيوت الزراعية. مقاوم للأشعة فوق البنفسجية.",
            "description_fr": "Plastique de haute qualité pour serres. Résistant aux UV.",
            "description_en": "High quality plastic for greenhouses. UV resistant.",
            "price": 8500,
            "stock": 40,
            "category_id": "cat_greenhouses",
            "images": ["https://images.pexels.com/photos/176169/pexels-photo-176169.jpeg"],
            "featured": False,
            "unit": "roll",
            "rating": 4.6,
            "reviews_count": 11,
            "created_at": datetime.now(timezone.utc).isoformat()
        }
    ]
    
    await db.products.insert_many(products)
    
    # Create admin user
    admin_user = {
        "user_id": f"user_{uuid.uuid4().hex[:12]}",
        "email": "admin@agroyousfi.dz",
        "name": "مدير المتجر",
        "role": "admin",
        "created_at": datetime.now(timezone.utc).isoformat()
    }
    await db.users.insert_one(admin_user)
    
    return {"message": "Database seeded successfully", "categories": len(categories), "products": len(products)}

# ============ WILAYA LIST ============

@api_router.get("/wilayas")
async def get_wilayas():
    """Get list of Algerian wilayas"""
    wilayas = [
        "أدرار", "الشلف", "الأغواط", "أم البواقي", "باتنة", "بجاية", "بسكرة", "بشار",
        "البليدة", "البويرة", "تمنراست", "تبسة", "تلمسان", "تيارت", "تيزي وزو", "الجزائر",
        "الجلفة", "جيجل", "سطيف", "سعيدة", "سكيكدة", "سيدي بلعباس", "عنابة", "قالمة",
        "قسنطينة", "المدية", "مستغانم", "المسيلة", "معسكر", "ورقلة", "وهران", "البيض",
        "إليزي", "برج بوعريريج", "بومرداس", "الطارف", "تندوف", "تيسمسيلت", "الوادي",
        "خنشلة", "سوق أهراس", "تيبازة", "ميلة", "عين الدفلى", "النعامة", "عين تموشنت",
        "غرداية", "غليزان", "تيميمون", "برج باجي مختار", "أولاد جلال", "بني عباس",
        "عين صالح", "عين قزام", "توقرت", "جانت", "المغير", "المنيعة"
    ]
    return wilayas

# Include router and middleware
app.include_router(api_router)

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()
