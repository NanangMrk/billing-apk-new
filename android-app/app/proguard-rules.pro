# ProGuard rules for Billing ISP
-keepattributes JavascriptInterface
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
-keepclassmembers class id.nanangmrk.billing.MainActivity$* {
    *;
}
