import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector'; // <-- ADDED THIS

const resources = {
  en: {
    translation: {
      "sunday_school": "Sunday School",
      "offline": "Offline",
      "syncing": "Syncing...",
      "pending": "Pending",
      "todays_classes": "Today's Classes",
      "no_classes": "You have no classes assigned for today.",
      "upcoming": "Upcoming",
      "open_present": "Open (Present)",
      "open_late": "Open (Late)",
      "closed": "Closed",
      "total": "Total",
      "marked": "Marked",
      "unmarked": "Unmarked",
      "search_students": "Search students...",
      "not_started_warning": "This session has not started yet. You cannot take attendance.",
      "closed_warning": "This session is closed. Modifications are disabled.",
      "present_btn": "P",
      "late_btn": "L",
      "permission_btn": "Per",
      "absent_btn": "A",
      "end_session": "Submit & End Session", 
      "session_ended": "Session Ended successfully.",
      "mark_rest_present": "Mark Rest as Present"
    }
  },
  am: {
    translation: {
      "sunday_school": "ሰንበት ትምህርት ቤት",
      "offline": "ከመስመር ውጭ",
      "syncing": "በማመሳሰል ላይ...",
      "pending": "በመጠባበቅ ላይ",
      "todays_classes": "የዛሬ ትምህርቶች",
      "no_classes": "ለዛሬ የተመደበ ትምህርት የለም።",
      "upcoming": "የሚመጣ",
      "open_present": "ክፍት (በሰዓቱ)",
      "open_late": "ክፍት (አርፍዷል)",
      "closed": "ተዘግቷል",
      "total": "ጠቅላላ",
      "marked": "የተመዘገቡ",
      "unmarked": "ያልተመዘገቡ",
      "search_students": "ተማሪዎችን ይፈልጉ...",
      "not_started_warning": "ይህ ትምህርት ገና አልተጀመረም። መዝገብ መውሰድ አይችሉም።",
      "closed_warning": "ይህ ትምህርት ተዘግቷል። ማስተካከል አይቻልም።",
      "present_btn": "ተገኝቷል",
      "late_btn": "አርፍዷል",
      "permission_btn": "ፍቃድ",
      "absent_btn": "ቀሪ",
      "end_session": "አስረክብ እና ትምህርቱን ዝጋ", 
      "session_ended": "ትምህርቱ በተሳካ ሁኔታ ተዘግቷል።",
      "mark_rest_present": "ቀሪዎቹን 'ተገኝቷል' ብለህ መዝግብ"
    }
  }
};

i18n
  .use(LanguageDetector) // <-- ADDED THIS PLUGIN
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: "en", // Used if localStorage is empty
    interpolation: {
      escapeValue: false 
    },
    detection: {
      order: ['localStorage', 'navigator'], // Look in localStorage first!
      caches: ['localStorage'], // Save changes back to localStorage
    }
  });

export default i18n;