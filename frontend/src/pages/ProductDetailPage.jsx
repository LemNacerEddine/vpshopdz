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
  Heart
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
      <div className="container mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-2 gap-12">
          <Skeleton className="aspect-square rounded-3xl" />
          <div className="space-y-4">
            <Skeleton className="h-8 w-3/4" />
            <Skeleton className="h-6 w-1/2" />
            <Skeleton className="h-24 w-full" />
            <Skeleton className="h-12 w-1/3" />
          </div>
        </div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <h2 className="text-2xl font-bold mb-4">{language === 'ar' ? 'المنتج غير موجود' : 'Product not found'}</h2>
        <Link to="/products">
          <Button className="rounded-full">{t('cart.continueShopping')}</Button>
        </Link>
      </div>
    );
  }

  const name = product[`name_${language}`] || product.name_ar;
  const description = product[`description_${language}`] || product.description_ar;
  const hasDiscount = product.old_price && product.old_price > product.price;
  const discountPercent = hasDiscount 
    ? Math.round((1 - product.price / product.old_price) * 100) 
    : 0;

  return (
    <div className="min-h-screen bg-background" data-testid="product-detail-page">
      {/* Breadcrumb */}
      <div className="bg-muted/30 py-4">
        <div className="container mx-auto px-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <Link to="/products" className="hover:text-primary">{t('nav.products')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{name}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-2 gap-12">
          {/* Images */}
          <div className="space-y-4">
            <div className="aspect-square rounded-3xl overflow-hidden bg-muted">
              <img
                src={product.images?.[selectedImage] || 'https://via.placeholder.com/600'}
                alt={name}
                className="w-full h-full object-cover"
              />
            </div>
            {product.images?.length > 1 && (
              <div className="flex gap-4 overflow-x-auto pb-2">
                {product.images.map((image, index) => (
                  <button
                    key={index}
                    onClick={() => setSelectedImage(index)}
                    className={`shrink-0 w-20 h-20 rounded-xl overflow-hidden border-2 transition-colors ${
                      selectedImage === index ? 'border-primary' : 'border-transparent'
                    }`}
                  >
                    <img src={image} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Details */}
          <div className="space-y-6">
            <div>
              {hasDiscount && (
                <Badge className="bg-secondary text-white mb-3">
                  -{discountPercent}% {language === 'ar' ? 'خصم' : 'OFF'}
                </Badge>
              )}
              <h1 className="text-3xl font-bold text-foreground mb-2">{name}</h1>
              
              {/* Rating */}
              {product.rating > 0 && (
                <div className="flex items-center gap-2">
                  <div className="flex">
                    {[...Array(5)].map((_, i) => (
                      <Star
                        key={i}
                        className={`h-5 w-5 ${i < Math.round(product.rating) ? 'fill-accent text-accent' : 'text-muted'}`}
                      />
                    ))}
                  </div>
                  <span className="font-medium">{product.rating}</span>
                  <span className="text-muted-foreground">
                    ({product.reviews_count} {t('products.reviews')})
                  </span>
                </div>
              )}
            </div>

            {/* Price */}
            <div className="flex items-baseline gap-3">
              <span className="text-4xl font-bold text-primary">
                {formatPrice(product.price)}
              </span>
              {hasDiscount && (
                <span className="text-xl text-muted-foreground line-through">
                  {formatPrice(product.old_price)}
                </span>
              )}
            </div>

            {/* Description */}
            <p className="text-muted-foreground leading-relaxed">
              {description}
            </p>

            {/* Stock */}
            <div>
              {product.stock > 0 ? (
                <Badge variant="outline" className="border-green-500 text-green-600">
                  {t('products.inStock')} ({product.stock} {product.unit})
                </Badge>
              ) : (
                <Badge variant="outline" className="border-destructive text-destructive">
                  {t('products.outOfStock')}
                </Badge>
              )}
            </div>

            {/* Quantity & Add to Cart */}
            <div className="flex flex-wrap items-center gap-4">
              <div className="flex items-center border rounded-full">
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  disabled={quantity <= 1}
                  className="rounded-full"
                >
                  <Minus className="h-4 w-4" />
                </Button>
                <span className="w-12 text-center font-semibold">{quantity}</span>
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                  disabled={quantity >= product.stock}
                  className="rounded-full"
                >
                  <Plus className="h-4 w-4" />
                </Button>
              </div>

              <Button
                size="lg"
                onClick={handleAddToCart}
                disabled={product.stock === 0}
                className="flex-1 rounded-full"
                data-testid="add-to-cart-btn"
              >
                <ShoppingCart className="h-5 w-5 me-2" />
                {t('products.addToCart')}
              </Button>

              {/* Wishlist Button */}
              <Button
                size="lg"
                variant={isInWishlist ? "secondary" : "outline"}
                onClick={handleToggleWishlist}
                disabled={wishlistLoading}
                className="rounded-full"
                data-testid="wishlist-btn"
              >
                <Heart className={`h-5 w-5 ${isInWishlist ? 'fill-current' : ''}`} />
              </Button>
            </div>

            {/* Features */}
            <div className="grid grid-cols-3 gap-4 pt-6 border-t">
              <div className="text-center">
                <div className="h-12 w-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                  <Truck className="h-6 w-6 text-primary" strokeWidth={1.5} />
                </div>
                <p className="text-xs text-muted-foreground">
                  {language === 'ar' ? 'توصيل سريع' : 'Fast Delivery'}
                </p>
              </div>
              <div className="text-center">
                <div className="h-12 w-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                  <Shield className="h-6 w-6 text-primary" strokeWidth={1.5} />
                </div>
                <p className="text-xs text-muted-foreground">
                  {language === 'ar' ? 'ضمان الجودة' : 'Quality Guarantee'}
                </p>
              </div>
              <div className="text-center">
                <div className="h-12 w-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                  <RotateCcw className="h-6 w-6 text-primary" strokeWidth={1.5} />
                </div>
                <p className="text-xs text-muted-foreground">
                  {language === 'ar' ? 'إرجاع سهل' : 'Easy Returns'}
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Reviews Section */}
        <div className="mt-16">
          <h2 className="text-2xl font-bold mb-8">{t('reviews.title')}</h2>
          
          <div className="grid lg:grid-cols-3 gap-8">
            {/* Write Review */}
            <div className="lg:col-span-1">
              <div className="bg-muted/30 rounded-3xl p-6">
                <h3 className="font-semibold mb-4">{t('reviews.writeReview')}</h3>
                
                {user ? (
                  <form onSubmit={handleSubmitReview} className="space-y-4">
                    <div>
                      <label className="text-sm text-muted-foreground mb-2 block">
                        {t('products.rating')}
                      </label>
                      <div className="flex gap-1">
                        {[1, 2, 3, 4, 5].map((star) => (
                          <button
                            key={star}
                            type="button"
                            onClick={() => setReviewRating(star)}
                            className="p-1"
                          >
                            <Star
                              className={`h-6 w-6 transition-colors ${
                                star <= reviewRating ? 'fill-accent text-accent' : 'text-muted'
                              }`}
                            />
                          </button>
                        ))}
                      </div>
                    </div>
                    
                    <div>
                      <label className="text-sm text-muted-foreground mb-2 block">
                        {t('reviews.comment')}
                      </label>
                      <Textarea
                        value={reviewComment}
                        onChange={(e) => setReviewComment(e.target.value)}
                        placeholder={language === 'ar' ? 'شاركنا تجربتك...' : 'Share your experience...'}
                        rows={4}
                      />
                    </div>
                    
                    <Button 
                      type="submit" 
                      className="w-full rounded-full"
                      disabled={submittingReview}
                    >
                      {submittingReview ? t('common.loading') : t('reviews.submit')}
                    </Button>
                  </form>
                ) : (
                  <div className="text-center py-4">
                    <p className="text-muted-foreground mb-4">
                      {language === 'ar' ? 'سجل دخولك لكتابة تقييم' : 'Login to write a review'}
                    </p>
                    <Link to="/login">
                      <Button variant="outline" className="rounded-full">
                        {t('nav.login')}
                      </Button>
                    </Link>
                  </div>
                )}
              </div>
            </div>

            {/* Reviews List */}
            <div className="lg:col-span-2 space-y-4">
              {reviews.length === 0 ? (
                <div className="text-center py-12 bg-muted/30 rounded-3xl">
                  <p className="text-muted-foreground">{t('reviews.noReviews')}</p>
                </div>
              ) : (
                reviews.map((review) => (
                  <div key={review.review_id} className="bg-card rounded-2xl p-6 border">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <p className="font-semibold">{review.user_name}</p>
                        <div className="flex gap-0.5 mt-1">
                          {[...Array(5)].map((_, i) => (
                            <Star
                              key={i}
                              className={`h-4 w-4 ${
                                i < review.rating ? 'fill-accent text-accent' : 'text-muted'
                              }`}
                            />
                          ))}
                        </div>
                      </div>
                      <span className="text-xs text-muted-foreground">
                        {new Date(review.created_at).toLocaleDateString(
                          language === 'ar' ? 'ar-DZ' : language === 'fr' ? 'fr-FR' : 'en-US'
                        )}
                      </span>
                    </div>
                    {review.comment && (
                      <p className="text-muted-foreground">{review.comment}</p>
                    )}
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductDetailPage;
