# Performance Report — Initial Screens (002)

## Web Bundle Sizes (next build — to be measured after first CI run)

| Route | Target | Status |
|---|---|---|
| guardian-web /login | < 120 KB gzip | pending CI |
| guardian-web /dashboard | < 200 KB gzip | pending CI |
| school-web /login | < 120 KB gzip | pending CI |
| school-web /dashboard | < 200 KB gzip | pending CI |

**Optimizations applied:**
- recharts: tree-shaken (only BarChart, AreaChart, PieChart imported)
- No dynamic imports on login pages (minimal JS)
- RSC for dashboard pages (zero client-side JS for data fetch)
- Suspense per section (streaming reduces TTI)

## Mobile TTI (guardian-mobile — to be measured with Flipper/Reactotron)

| Target | Status |
|---|---|
| Dashboard TTI < 2s on 4G | pending device test |

**Optimizations applied:**
- TanStack Query with 30s staleTime (prevents waterfall on re-visit)
- expo-image for optimized image decoding
- FlatList with `removeClippedSubviews` for ChildCarousel
