import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Textarea } from '@/components/ui/textarea';
import { ProductGallery } from '@/components/products/ProductGallery';
import { toast } from 'sonner';
import { 
  Star, 
  ShoppingCart, 
  Minus, 
  Plus, 
  ChevronRight, 
  ChevronLeft,
  Truck,
  Shield,
  RotateCcw,
  Heart,
  Share2,
  Clock,
  Tag,
  CheckCircle,
  Package
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const ProductDetailPage = () => {
  const { productId } = useParams();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { addToCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();
  
  const [product, setProduct] = useState(null);
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [selectedImage, setSelectedImage] = useState(0);
  const [reviewRating, setReviewRating] = useState(5);
  const [reviewComment, setReviewComment] = useState('');
  const [submittingReview, setSubmittingReview] = useState(false);
  const [isInWishlist, setIsInWishlist] = useState(false);
  const [wishlistLoading, setWishlistLoading] = useState(false);

  useEffect(() => {
    fetchProduct();
    fetchReviews();
    if (user) {
      checkWishlistStatus();
    }
  }, [productId, user]);

  const checkWishlistStatus = async () => {
    try {
      const response = await axios.get(`${API}/wishlist`, { withCredentials: true });
      const inWishlist = response.data.some(item => item.product_id === productId);
      setIsInWishlist(inWishlist);
    } catch (error) {
      console.error('Error checking wishlist:', error);
    }
  };

  const handleToggleWishlist = async () => {
    if (!user) {
      toast.error(language === 'ar' ? 'يجب تسجيل الدخول أولاً' : 'Please login first');
      navigate('/login');
      return;
    }

    try {
      setWishlistLoading(true);
      if (isInWishlist) {
        await axios.delete(`${API}/wishlist/${productId}`, { withCredentials: true });
        setIsInWishlist(false);
        toast.success(language === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from wishlist');
      } else {
        await axios.post(`${API}/wishlist/${productId}`, {}, { withCredentials: true });
        setIsInWishlist(true);
        toast.success(language === 'ar' ? 'تمت الإضافة للمفضلة' : 'Added to wishlist');
      }
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setWishlistLoading(false);
    }
  };

  const fetchProduct = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/products/${productId}`);
      setProduct(response.data);
    } catch (error) {
      console.error('Error fetching product:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchReviews = async () => {
    try {
      const response = await axios.get(`${API}/reviews/${productId}`);
      setReviews(response.data);
    } catch (error) {
      console.error('Error fetching reviews:', error);
    }
  };

  const handleAddToCart = async () => {
    const success = await addToCart(product.product_id, quantity);
    if (success) {
      toast.success(t('products.addToCart'), {
        description: `${quantity}x ${product[`name_${language}`] || product.name_ar}`
      });
    }
  };

  const handleSubmitReview = async (e) => {
    e.preventDefault();
    if (!user) {
      toast.error(language === 'ar' ? 'يجب تسجيل الدخول أولاً' : 'Please login first');
      return;
    }

    try {
      setSubmittingReview(true);
      await axios.post(`${API}/reviews`, {
        product_id: productId,
        rating: reviewRating,
        comment: reviewComment
      }, { withCredentials: true });
      
      toast.success(t('reviews.submit'));
      setReviewComment('');
      setReviewRating(5);
      fetchReviews();
      fetchProduct();
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setSubmittingReview(false);
    }
  };

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-6">
        <div className="grid lg:grid-cols-2 gap-8">
          <div className="flex gap-3">
            <div className="flex flex-col gap-2">
              {[1,2,3,4].map(i => <Skeleton key={i} className="w-16 h-16 rounded-lg" />)}
            </div>
            <Skeleton className="flex-1 aspect-square rounded-xl max-w-md" />
          </div>
          <div className="space-y-4">
            <Skeleton className="h-6 w-3/4" />
            <Skeleton className="h-5 w-1/2" />
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-10 w-1/3" />
          </div>
        </div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <p className="text-muted-foreground">{t('products.notFound')}</p>
      </div>
    );
  }

  const name = product[`name_${language}`] || product.name_ar;
  const description = product[`description_${language}`] || product.description_ar;
  const discountPercent = product.old_price 
    ? Math.round((1 - product.price / product.old_price) * 100) 
    : 0;

  const l = {
    ar: {
      freeShipping: 'شحن مجاني',
      freeShippingDesc: 'للطلبات فوق 5000 دج',
      limitedOffer: 'عرض محدود',
      endsIn: 'ينتهي في',
      sold: 'تم بيعه',
      inStock: 'متوفر',
      outOfStock: 'غير متوفر',
      quantity: 'الكمية',
      addToCart: 'أضف إلى السلة',
      buyNow: 'اشتري الآن',
      delivery: 'التوصيل',
      deliveryTime: '3-7 أيام عمل',
      securePayment: 'دفع آمن',
      guarantee: 'ضمان الجودة',
      easyReturn: 'إرجاع سهل',
      reviews: 'التقييمات',
      writeReview: 'اكتب تقييم',
      noReviews: 'لا توجد تقييمات بعد',
      save: 'وفر'
    },
    fr: {
      freeShipping: 'Livraison gratuite',
      freeShippingDesc: 'Pour les commandes de plus de 5000 DZD',
      limitedOffer: 'Offre limitée',
      endsIn: 'Se termine dans',
      sold: 'Vendu',
      inStock: 'En stock',
      outOfStock: 'Rupture de stock',
      quantity: 'Quantité',
      addToCart: 'Ajouter au panier',
      buyNow: 'Acheter maintenant',
      delivery: 'Livraison',
      deliveryTime: '3-7 jours ouvrables',
      securePayment: 'Paiement sécurisé',
      guarantee: 'Garantie de qualité',
      easyReturn: 'Retour facile',
      reviews: 'Avis',
      writeReview: 'Écrire un avis',
      noReviews: 'Pas encore d\'avis',
      save: 'Économisez'
    },
    en: {
      freeShipping: 'Free Shipping',
      freeShippingDesc: 'For orders over 5000 DZD',
      limitedOffer: 'Limited Offer',
      endsIn: 'Ends in',
      sold: 'Sold',
      inStock: 'In Stock',
      outOfStock: 'Out of Stock',
      quantity: 'Quantity',
      addToCart: 'Add to Cart',
      buyNow: 'Buy Now',
      delivery: 'Delivery',
      deliveryTime: '3-7 business days',
      securePayment: 'Secure Payment',
      guarantee: 'Quality Guarantee',
      easyReturn: 'Easy Return',
      reviews: 'Reviews',
      writeReview: 'Write a Review',
      noReviews: 'No reviews yet',
      save: 'Save'
    }
  };

  const text = l[language] || l.ar;

  return (
    <div className="min-h-screen bg-background" data-testid="product-detail">
      {/* Top Banner - Like Temu */}
      <div className="bg-gradient-to-r from-red-600 to-red-500 text-white py-2">
        <div className="container mx-auto px-4 flex items-center justify-center gap-4 text-sm">
          <div className="flex items-center gap-1">
            <Truck className="h-4 w-4" />
            <span>{text.freeShipping}</span>
          </div>
          <span className="opacity-50">|</span>
          <div className="flex items-center gap-1">
            <Shield className="h-4 w-4" />
            <span>{text.securePayment}</span>
          </div>
        </div>
      </div>

      {/* Breadcrumb */}
      <div className="bg-muted/30 py-2">
        <div className="container mx-auto px-4">
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-3 w-3" />
            <Link to="/products" className="hover:text-primary">{t('nav.products')}</Link>
            <ChevronIcon className="h-3 w-3" />
            <span className="text-foreground truncate max-w-[200px]">{name}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-4">
        <div className={`grid lg:grid-cols-5 gap-6 ${isRTL ? '' : ''}`}>
          {/* Product Info - Temu Style (3 columns) - First in RTL */}
          <div className={`lg:col-span-3 space-y-4 ${isRTL ? 'lg:order-1' : 'lg:order-2'}`}>
            {/* Title & Rating */}
            <div>
              <h1 className="text-xl font-bold text-foreground leading-tight mb-2">
                {name}
              </h1>
              <div className="flex items-center gap-3 text-sm">
                <div className="flex items-center gap-1">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                      key={star}
                      className={`h-4 w-4 ${
                        star <= Math.round(product.rating)
                          ? 'fill-yellow-400 text-yellow-400'
                          : 'text-gray-200'
                      }`}
                    />
                  ))}
                  <span className="text-muted-foreground ms-1">{product.rating}</span>
                </div>
                <span className="text-muted-foreground">|</span>
                <span className="text-muted-foreground">{product.reviews_count} {text.reviews}</span>
                <span className="text-muted-foreground">|</span>
                <span className="text-muted-foreground">{product.stock > 50 ? '50+' : product.stock} {text.sold}</span>
              </div>
            </div>

            {/* Price Section - Temu Style */}
            <div className="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-950/20 dark:to-orange-950/20 rounded-xl p-4 border border-red-100 dark:border-red-900/30">
              {/* Discount Badge */}
              {discountPercent > 0 && (
                <div className="flex items-center gap-2 mb-2">
                  <Badge className="bg-red-600 text-white hover:bg-red-600 rounded-sm px-2 py-0.5 text-xs font-bold">
                    -{discountPercent}%
                  </Badge>
                  <div className="flex items-center gap-1 text-xs text-red-600">
                    <Clock className="h-3 w-3" />
                    <span>{text.limitedOffer}</span>
                  </div>
                </div>
              )}
              
              {/* Price */}
              <div className="flex items-baseline gap-3">
                <span className="text-3xl font-bold text-red-600">
                  {formatPrice(product.price)}
                </span>
                {product.old_price && (
                  <>
                    <span className="text-lg text-muted-foreground line-through">
                      {formatPrice(product.old_price)}
                    </span>
                    <Badge variant="secondary" className="text-green-600 bg-green-50 text-xs">
                      {text.save} {formatPrice(product.old_price - product.price)}
                    </Badge>
                  </>
                )}
              </div>
            </div>

            {/* Stock Status */}
            <div className="flex items-center gap-2">
              {product.stock > 0 ? (
                <>
                  <CheckCircle className="h-4 w-4 text-green-600" />
                  <span className="text-green-600 text-sm font-medium">{text.inStock}</span>
                  <span className="text-muted-foreground text-sm">({product.stock} {product.unit})</span>
                </>
              ) : (
                <span className="text-red-500 text-sm font-medium">{text.outOfStock}</span>
              )}
            </div>

            {/* Description */}
            {description && (
              <p className="text-sm text-muted-foreground leading-relaxed">
                {description}
              </p>
            )}

            {/* Quantity Selector */}
            <div className="flex items-center gap-4">
              <span className="text-sm font-medium">{text.quantity}</span>
              <div className="flex items-center border rounded-lg overflow-hidden">
                <Button
                  variant="ghost"
                  size="icon"
                  className="h-9 w-9 rounded-none"
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  disabled={quantity <= 1}
                >
                  <Minus className="h-4 w-4" />
                </Button>
                <span className="w-12 text-center font-medium">{quantity}</span>
                <Button
                  variant="ghost"
                  size="icon"
                  className="h-9 w-9 rounded-none"
                  onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                  disabled={quantity >= product.stock}
                >
                  <Plus className="h-4 w-4" />
                </Button>
              </div>
            </div>

            {/* Action Buttons - Temu Style */}
            <div className="flex gap-3 pt-2">
              <Button
                size="lg"
                onClick={handleAddToCart}
                disabled={product.stock === 0}
                className="flex-1 rounded-full bg-red-600 hover:bg-red-700 h-12 text-base font-bold"
                data-testid="add-to-cart-btn"
              >
                <ShoppingCart className="h-5 w-5 me-2" />
                {text.addToCart}
              </Button>

              <Button
                size="lg"
                variant={isInWishlist ? "secondary" : "outline"}
                onClick={handleToggleWishlist}
                disabled={wishlistLoading}
                className="h-12 w-12 rounded-full"
                data-testid="wishlist-btn"
              >
                <Heart className={`h-5 w-5 ${isInWishlist ? 'fill-red-500 text-red-500' : ''}`} />
              </Button>
            </div>

            {/* Features - Temu Style */}
            <div className="grid grid-cols-3 gap-3 pt-4 border-t">
              <div className="text-center p-3 rounded-lg bg-muted/50">
                <Truck className="h-5 w-5 mx-auto text-primary mb-1" />
                <p className="text-xs font-medium">{text.delivery}</p>
                <p className="text-xs text-muted-foreground">{text.deliveryTime}</p>
              </div>
              <div className="text-center p-3 rounded-lg bg-muted/50">
                <Shield className="h-5 w-5 mx-auto text-primary mb-1" />
                <p className="text-xs font-medium">{text.guarantee}</p>
              </div>
              <div className="text-center p-3 rounded-lg bg-muted/50">
                <RotateCcw className="h-5 w-5 mx-auto text-primary mb-1" />
                <p className="text-xs font-medium">{text.easyReturn}</p>
              </div>
            </div>
          </div>

          {/* Images/Video Gallery - Temu Style (2 columns) */}
          <div className={`lg:col-span-2 ${isRTL ? 'lg:order-2' : 'lg:order-1'}`}>
            <div className="sticky top-20">
              <ProductGallery 
                images={product.images || []}
                video={product.video || null}
                productName={name}
                isRTL={isRTL}
              />
            </div>
          </div>
        </div>

        {/* Reviews Section */}
        <div className="mt-8 pt-8 border-t">
          <h2 className="text-xl font-bold mb-6">{text.reviews} ({product.reviews_count})</h2>
          
          {/* Write Review Form */}
          {user && (
            <form onSubmit={handleSubmitReview} className="mb-8 p-4 bg-muted/30 rounded-xl">
              <h3 className="font-medium mb-4">{text.writeReview}</h3>
              <div className="flex items-center gap-2 mb-4">
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    type="button"
                    onClick={() => setReviewRating(star)}
                    className="focus:outline-none"
                  >
                    <Star
                      className={`h-7 w-7 transition-colors ${
                        star <= reviewRating
                          ? 'fill-yellow-400 text-yellow-400'
                          : 'text-gray-300 hover:text-yellow-300'
                      }`}
                    />
                  </button>
                ))}
              </div>
              <Textarea
                value={reviewComment}
                onChange={(e) => setReviewComment(e.target.value)}
                placeholder={language === 'ar' ? 'اكتب تعليقك هنا...' : 'Write your comment...'}
                className="mb-4 resize-none"
                rows={3}
              />
              <Button 
                type="submit" 
                disabled={submittingReview || !reviewComment.trim()}
                className="rounded-full"
              >
                {submittingReview ? t('common.loading') : t('reviews.submit')}
              </Button>
            </form>
          )}

          {/* Reviews List */}
          {reviews.length > 0 ? (
            <div className="space-y-4">
              {reviews.map((review, index) => (
                <div key={index} className="p-4 bg-card rounded-xl border">
                  <div className="flex items-center gap-3 mb-2">
                    <div className="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center">
                      <span className="text-sm font-medium text-primary">
                        {review.user_name?.charAt(0) || 'U'}
                      </span>
                    </div>
                    <div>
                      <p className="font-medium text-sm">{review.user_name || 'مستخدم'}</p>
                      <div className="flex items-center gap-1">
                        {[1, 2, 3, 4, 5].map((star) => (
                          <Star
                            key={star}
                            className={`h-3 w-3 ${
                              star <= review.rating
                                ? 'fill-yellow-400 text-yellow-400'
                                : 'text-gray-200'
                            }`}
                          />
                        ))}
                      </div>
                    </div>
                  </div>
                  <p className="text-sm text-muted-foreground">{review.comment}</p>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-center text-muted-foreground py-8">{text.noReviews}</p>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductDetailPage;
