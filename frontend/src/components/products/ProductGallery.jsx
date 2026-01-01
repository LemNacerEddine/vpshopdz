import React, { useState, useRef, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Play, Pause, ZoomIn, X, Maximize2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent } from '@/components/ui/dialog';

export const ProductGallery = ({ images = [], video = null, productName = '', isRTL = false }) => {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [isZoomed, setIsZoomed] = useState(false);
  const [zoomPosition, setZoomPosition] = useState({ x: 50, y: 50 });
  const [isVideoPlaying, setIsVideoPlaying] = useState(false);
  const [showLightbox, setShowLightbox] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const mainImageRef = useRef(null);
  const videoRef = useRef(null);

  // Combine images and video into media array
  const media = [
    ...(video ? [{ type: 'video', src: video }] : []),
    ...images.map(img => ({ type: 'image', src: img }))
  ];

  const currentMedia = media[selectedIndex] || { type: 'image', src: 'https://via.placeholder.com/600' };

  const handleMouseMove = (e) => {
    if (!mainImageRef.current || currentMedia.type === 'video') return;
    
    const rect = mainImageRef.current.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    setZoomPosition({ x, y });
  };

  const handlePrev = () => {
    setSelectedIndex((prev) => (prev === 0 ? media.length - 1 : prev - 1));
    setIsVideoPlaying(false);
  };

  const handleNext = () => {
    setSelectedIndex((prev) => (prev === media.length - 1 ? 0 : prev + 1));
    setIsVideoPlaying(false);
  };

  const toggleVideo = () => {
    if (videoRef.current) {
      if (isVideoPlaying) {
        videoRef.current.pause();
      } else {
        videoRef.current.play();
      }
      setIsVideoPlaying(!isVideoPlaying);
    }
  };

  const openLightbox = (index) => {
    setLightboxIndex(index);
    setShowLightbox(true);
  };

  useEffect(() => {
    if (currentMedia.type !== 'video' && videoRef.current) {
      videoRef.current.pause();
      setIsVideoPlaying(false);
    }
  }, [selectedIndex]);

  return (
    <div className={`flex gap-3 ${isRTL ? '' : 'flex-row-reverse'}`}>
      {/* Vertical Thumbnails - Like Temu/AliExpress */}
      {media.length > 1 && (
        <div className="flex flex-col gap-2 w-16 shrink-0">
          {media.map((item, index) => (
            <button
              key={index}
              onMouseEnter={() => setSelectedIndex(index)}
              onClick={() => setSelectedIndex(index)}
              className={`relative w-16 h-16 rounded-lg overflow-hidden border-2 transition-all ${
                selectedIndex === index 
                  ? 'border-primary ring-1 ring-primary/30' 
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              {item.type === 'video' ? (
                <div className="w-full h-full bg-muted relative">
                  <video 
                    src={item.src} 
                    className="w-full h-full object-cover"
                    muted
                  />
                  <div className="absolute inset-0 flex items-center justify-center bg-black/30">
                    <Play className="h-5 w-5 text-white" fill="white" />
                  </div>
                </div>
              ) : (
                <img 
                  src={item.src} 
                  alt={`${productName} ${index + 1}`}
                  className="w-full h-full object-cover"
                />
              )}
            </button>
          ))}
        </div>
      )}

      {/* Main Image/Video Container - Smaller size like Temu */}
      <div className="relative group flex-1 max-w-md">
        <div 
          ref={mainImageRef}
          className="aspect-square rounded-xl overflow-hidden bg-muted relative cursor-zoom-in"
          onMouseEnter={() => currentMedia.type === 'image' && setIsZoomed(true)}
          onMouseLeave={() => setIsZoomed(false)}
          onMouseMove={handleMouseMove}
          onClick={() => openLightbox(selectedIndex)}
        >
          {currentMedia.type === 'video' ? (
            <div className="relative w-full h-full">
              <video
                ref={videoRef}
                src={currentMedia.src}
                className="w-full h-full object-cover"
                loop
                playsInline
                onClick={(e) => {
                  e.stopPropagation();
                  toggleVideo();
                }}
              />
              <div 
                className={`absolute inset-0 flex items-center justify-center bg-black/20 transition-opacity ${
                  isVideoPlaying ? 'opacity-0 hover:opacity-100' : 'opacity-100'
                }`}
                onClick={(e) => {
                  e.stopPropagation();
                  toggleVideo();
                }}
              >
                <div className="h-14 w-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg cursor-pointer hover:scale-110 transition-transform">
                  {isVideoPlaying ? (
                    <Pause className="h-7 w-7 text-primary" />
                  ) : (
                    <Play className="h-7 w-7 text-primary ms-1" />
                  )}
                </div>
              </div>
              <div className="absolute top-3 start-3 bg-black/70 text-white px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                <Play className="h-3 w-3" />
                فيديو
              </div>
            </div>
          ) : (
            <>
              <img
                src={currentMedia.src}
                alt={productName}
                className={`w-full h-full object-cover transition-transform duration-300 ${
                  isZoomed ? 'scale-150' : 'scale-100'
                }`}
                style={isZoomed ? {
                  transformOrigin: `${zoomPosition.x}% ${zoomPosition.y}%`
                } : {}}
              />
              <div className="absolute bottom-3 end-3 bg-black/50 text-white p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                <ZoomIn className="h-4 w-4" />
              </div>
            </>
          )}
        </div>

        {/* Navigation Arrows */}
        {media.length > 1 && (
          <>
            <Button
              variant="secondary"
              size="icon"
              className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-1' : 'left-1'} h-8 w-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-md`}
              onClick={(e) => {
                e.stopPropagation();
                isRTL ? handleNext() : handlePrev();
              }}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <Button
              variant="secondary"
              size="icon"
              className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'left-1' : 'right-1'} h-8 w-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-md`}
              onClick={(e) => {
                e.stopPropagation();
                isRTL ? handlePrev() : handleNext();
              }}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
          </>
        )}

        {/* Fullscreen Button */}
        <Button
          variant="secondary"
          size="icon"
          className="absolute top-2 end-2 h-8 w-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-md"
          onClick={(e) => {
            e.stopPropagation();
            openLightbox(selectedIndex);
          }}
        >
          <Maximize2 className="h-4 w-4" />
        </Button>

        {/* Image Counter */}
        <div className="absolute bottom-2 start-2 bg-black/60 text-white px-2 py-0.5 rounded-full text-xs">
          {selectedIndex + 1} / {media.length}
        </div>
      </div>

      {/* Lightbox Modal */}
      <Dialog open={showLightbox} onOpenChange={setShowLightbox}>
        <DialogContent className="max-w-4xl w-full h-[85vh] p-0 bg-black border-none">
          <div className="relative w-full h-full flex items-center justify-center">
            <Button
              variant="ghost"
              size="icon"
              className="absolute top-3 end-3 z-50 text-white hover:bg-white/20 rounded-full"
              onClick={() => setShowLightbox(false)}
            >
              <X className="h-5 w-5" />
            </Button>

            {media.length > 1 && (
              <>
                <Button
                  variant="ghost"
                  size="icon"
                  className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-10 w-10 rounded-full text-white hover:bg-white/20`}
                  onClick={() => setLightboxIndex(prev => prev === 0 ? media.length - 1 : prev - 1)}
                >
                  <ChevronLeft className="h-6 w-6" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  className={`absolute ${isRTL ? 'left-3' : 'right-3'} top-1/2 -translate-y-1/2 h-10 w-10 rounded-full text-white hover:bg-white/20`}
                  onClick={() => setLightboxIndex(prev => prev === media.length - 1 ? 0 : prev + 1)}
                >
                  <ChevronRight className="h-6 w-6" />
                </Button>
              </>
            )}

            <div className="max-w-3xl max-h-[75vh] px-14">
              {media[lightboxIndex]?.type === 'video' ? (
                <video
                  src={media[lightboxIndex].src}
                  className="max-w-full max-h-[75vh] object-contain"
                  controls
                  autoPlay
                />
              ) : (
                <img
                  src={media[lightboxIndex]?.src}
                  alt={productName}
                  className="max-w-full max-h-[75vh] object-contain"
                />
              )}
            </div>

            <div className="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-white px-3 py-1.5 rounded-full text-sm">
              {lightboxIndex + 1} / {media.length}
            </div>

            <div className="absolute bottom-12 left-1/2 -translate-x-1/2 flex gap-1.5 max-w-md overflow-x-auto p-1.5">
              {media.map((item, index) => (
                <button
                  key={index}
                  onClick={() => setLightboxIndex(index)}
                  className={`shrink-0 w-10 h-10 rounded-md overflow-hidden border-2 transition-all ${
                    lightboxIndex === index ? 'border-white' : 'border-transparent opacity-50 hover:opacity-100'
                  }`}
                >
                  {item.type === 'video' ? (
                    <div className="w-full h-full bg-gray-800 flex items-center justify-center">
                      <Play className="h-3 w-3 text-white" />
                    </div>
                  ) : (
                    <img src={item.src} alt="" className="w-full h-full object-cover" />
                  )}
                </button>
              ))}
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ProductGallery;
