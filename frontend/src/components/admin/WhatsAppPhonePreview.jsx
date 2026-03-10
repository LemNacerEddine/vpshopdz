import React from 'react';
import { CheckCheck } from 'lucide-react';

/**
 * WhatsApp Phone Mockup Preview
 * Renders a phone-shaped frame with a WhatsApp-style chat bubble.
 * Props:
 *   message     – The message text to display (supports WhatsApp formatting: *bold*, _italic_, ~strike~)
 *   storeName   – Name shown in the chat header (default: "AgroYousfi")
 *   time        – Message timestamp (default: current HH:MM)
 *   className   – Additional CSS classes for the outer wrapper
 */
const WhatsAppPhonePreview = ({ message = '', storeName = 'AgroYousfi', time, className = '' }) => {
  const displayTime = time || new Date().toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' });

  // Convert WhatsApp-style formatting to HTML
  const formatMessage = (text) => {
    if (!text) return '';
    let formatted = text
      .replace(/\*(.*?)\*/g, '<strong>$1</strong>')
      .replace(/_(.*?)_/g, '<em>$1</em>')
      .replace(/~(.*?)~/g, '<del>$1</del>')
      .replace(/\n/g, '<br/>');
    return formatted;
  };

  return (
    <div className={`flex justify-center ${className}`}>
      {/* Phone Frame */}
      <div className="w-[280px] h-[500px] bg-black rounded-[36px] p-[8px] shadow-2xl relative">
        {/* Notch */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[100px] h-[24px] bg-black rounded-b-2xl z-10" />

        {/* Screen */}
        <div className="w-full h-full bg-[#efeae2] rounded-[28px] overflow-hidden flex flex-col">
          {/* WhatsApp Header */}
          <div className="bg-[#075e54] px-3 py-2 pt-6 flex items-center gap-2 shrink-0">
            <div className="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden">
              <svg viewBox="0 0 24 24" className="w-5 h-5 text-gray-500" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
              </svg>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-xs font-medium truncate">{storeName}</p>
              <p className="text-green-200 text-[10px]">online</p>
            </div>
          </div>

          {/* Chat Area */}
          <div
            className="flex-1 p-3 overflow-y-auto"
            style={{
              backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8c3ba' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`,
            }}
          >
            {message ? (
              <div className="max-w-[220px] ms-auto">
                {/* Green bubble (outgoing message) */}
                <div className="bg-[#dcf8c6] rounded-lg rounded-tr-none p-2 shadow-sm relative">
                  <p
                    className="text-[11px] text-gray-800 leading-relaxed break-words whitespace-pre-wrap"
                    dangerouslySetInnerHTML={{ __html: formatMessage(message) }}
                  />
                  <div className="flex items-center justify-end gap-1 mt-1">
                    <span className="text-[9px] text-gray-500">{displayTime}</span>
                    <CheckCheck className="w-3 h-3 text-blue-500" />
                  </div>
                </div>
              </div>
            ) : (
              <div className="flex items-center justify-center h-full">
                <p className="text-[10px] text-gray-400 text-center">
                  Message preview will appear here
                </p>
              </div>
            )}
          </div>

          {/* Input Bar */}
          <div className="bg-[#f0f0f0] px-2 py-2 flex items-center gap-2 shrink-0">
            <div className="flex-1 bg-white rounded-full px-3 py-1.5">
              <p className="text-[10px] text-gray-400">Type a message</p>
            </div>
            <div className="w-7 h-7 bg-[#075e54] rounded-full flex items-center justify-center">
              <svg viewBox="0 0 24 24" className="w-3.5 h-3.5 text-white" fill="currentColor">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WhatsAppPhonePreview;
