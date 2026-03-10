import React, { useState } from 'react';
import axios from 'axios';

const API = import.meta.env.VITE_API_URL || 'https://vpdeveloper.dz/agro-yousfi/api';

const GoogleSignInButton = ({ onSuccess, onError }) => {
  const [loading, setLoading] = useState(false);

  const handleGoogleSignIn = async () => {
    try {
      setLoading(true);

      // Get Google OAuth URL from backend
      const response = await axios.get(`${API}/auth/google`);
      const { authUrl } = response.data;

      // Open Google OAuth in popup
      const width = 500;
      const height = 600;
      const left = window.screen.width / 2 - width / 2;
      const top = window.screen.height / 2 - height / 2;

      const popup = window.open(
        authUrl,
        'Google Sign In',
        `width=${width},height=${height},left=${left},top=${top}`
      );

      // Listen for callback
      const checkPopup = setInterval(() => {
        try {
          if (popup.closed) {
            clearInterval(checkPopup);
            setLoading(false);
            return;
          }

          // Check if popup URL contains callback
          const popupUrl = popup.location.href;
          
          if (popupUrl.includes('/auth/google/callback')) {
            clearInterval(checkPopup);
            
            // Extract code from URL
            const urlParams = new URLSearchParams(popup.location.search);
            const code = urlParams.get('code');

            if (code) {
              // Exchange code for user data
              handleCallback(code);
              popup.close();
            }
          }
        } catch (e) {
          // Cross-origin error - popup still on Google domain
        }
      }, 500);

    } catch (error) {
      console.error('Google Sign In error:', error);
      setLoading(false);
      if (onError) onError(error);
    }
  };

  const handleCallback = async (code) => {
    try {
      // Send code to backend
      const response = await axios.get(`${API}/auth/google/callback?code=${code}`, {
        withCredentials: true
      });

      if (response.data.success) {
        console.log('Google Sign In successful:', response.data.user);
        if (onSuccess) onSuccess(response.data.user);
      }
    } catch (error) {
      console.error('Callback error:', error);
      setLoading(false);
      if (onError) onError(error);
    }
  };

  return (
    <button
      onClick={handleGoogleSignIn}
      disabled={loading}
      className="w-full flex items-center justify-center gap-3 px-4 py-3 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {/* Google Icon */}
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M19.6 10.227c0-.709-.064-1.39-.182-2.045H10v3.868h5.382a4.6 4.6 0 01-1.996 3.018v2.51h3.232c1.891-1.742 2.982-4.305 2.982-7.35z" fill="#4285F4"/>
        <path d="M10 20c2.7 0 4.964-.895 6.618-2.423l-3.232-2.509c-.895.6-2.04.955-3.386.955-2.605 0-4.81-1.76-5.595-4.123H1.064v2.59A9.996 9.996 0 0010 20z" fill="#34A853"/>
        <path d="M4.405 11.9c-.2-.6-.314-1.24-.314-1.9 0-.66.114-1.3.314-1.9V5.51H1.064A9.996 9.996 0 000 10c0 1.614.386 3.14 1.064 4.49l3.34-2.59z" fill="#FBBC05"/>
        <path d="M10 3.977c1.468 0 2.786.505 3.823 1.496l2.868-2.868C14.959.99 12.695 0 10 0 6.09 0 2.71 2.24 1.064 5.51l3.34 2.59C5.19 5.736 7.395 3.977 10 3.977z" fill="#EA4335"/>
      </svg>

      <span className="text-gray-700 font-medium">
        {loading ? 'جاري التحميل...' : 'تسجيل الدخول عبر Google'}
      </span>
    </button>
  );
};

export default GoogleSignInButton;
