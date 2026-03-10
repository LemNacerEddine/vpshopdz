import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import "@/index.css";
import App from "@/App";

const basename = process.env.NODE_ENV === "production" ? "/agro-yousfi" : "/agro-store";

// Handle Google OAuth callback BEFORE React renders
const urlParams = new URLSearchParams(window.location.search);
const googleAuth = urlParams.get('google_auth');
const userData = urlParams.get('user');

if (googleAuth === 'success' && userData) {
    try {
        const decodedUser = atob(userData);
        const user = JSON.parse(decodedUser);
        localStorage.setItem('agroyousfi_user', JSON.stringify(user));

        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    } catch (e) {
        console.error('Failed to process Google OAuth user data:', e);
    }
}

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(
    <React.StrictMode>
        <BrowserRouter basename={basename}>
            <App />
        </BrowserRouter>
    </React.StrictMode>
);
