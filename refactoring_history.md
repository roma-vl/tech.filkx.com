# Refactoring History Log

This document tracks the steps taken during the Vue 3 + TypeScript SPA frontend refactoring to Feature-Sliced Design (FSD).

## Refactoring Steps

### Phase 1: Backend Middleware for Case Standardization
- Created Laravel middleware `ConvertCamelCaseToSnakeCase` in `api/app/Http/Middleware/ConvertCamelCaseToSnakeCase.php`.
- Registered middleware in `api/bootstrap/app.php` to run globally.
- **Why**: Allows frontend to send camelCase requests to the backend without modifying validation rules or endpoint logic (as the backend already maps responses to camelCase).

### Phase 2: Project TypeScript Setup
- Added `typescript`, `vue-tsc`, and `@types/node` to `package.json` devDependencies.
- Created `tsconfig.json` and `tsconfig.node.json` in the frontend root.
- Created global TypeScript type definitions shim `src/vite-env.d.ts`.

### Phase 3: FSD Folder Setup & Shared Layer
- Created directory structure for FSD under `src/`: `app/`, `shared/`, `entities/`, `features/`, `widgets/`, `pages/`.
- Copied and flattened all UI library elements into `src/shared/ui/` and exported them via `src/shared/ui/index.ts`.
- Created robust TS Axios client `src/shared/services/api/apiClient.ts` with interceptors (auth header injection, auto-refresh token on 401, error handling redirect).
- Created domain specific API files under `shared/services/api/`: `authApi.ts`, `productApi.ts`, `orderApi.ts`, `billingApi.ts`, `accountingApi.ts`.
- Created shared composables in `shared/composables/`: `usePagination`, `useSorting`, `useModal`, `useDebounce`, `useFilters`.
- Created entities type definitions in `src/entities/`: `product/types.ts`, `category/types.ts`, `order/types.ts`, `user/types.ts`, `invoice/types.ts`, `customer/types.ts`.
- Created shared constants: `src/shared/constants/order.ts`, `src/shared/constants/user.ts`.
- Created mapper utility `src/shared/utils/mappers.ts` for casing and DTO conversions.

### Phase 4: State Management Migration (Pinia)
- Developed new Pinia stores with strict TypeScript definitions:
  - `src/entities/order/model/cartStore.ts` (replaces legacy cart logic with full DTO mappings and dynamic backend syncing).
  - `src/entities/user/model/authStore.ts` (replaces legacy auth logic, handles token storage and user sessions).
  - `src/entities/user/model/systemStore.ts` (manages API connection checking).
  - `src/shared/model/uiStore.ts` (manages dark/light theme switching).
  - `src/entities/user/model/subscriptionStore.ts` (stub store for features restrictions).
- Refactored legacy store files (`src/store.js`, `src/stores/auth.js`, `src/stores/system.js`, `src/stores/ui.js`, `src/stores/subscription.js`) as reactive compatibility proxies. This keeps existing legacy components operational while they wait to be migrated.

### Phase 5: Component Modularization & FSD Pages Refactoring
- Refactored `AdminOrders.vue` from a monolithic single-file implementation into clean FSD layers:
  - **Business Logic Composable**: Created `src/features/orders/composables/useOrders.ts` to manage search queries, multi-filter actions, CSV export, detail modal states, status change actions, and formatting.
  - **FSD Widgets**:
    - `src/widgets/OrderTable/OrderFiltersWidget.vue` (handles UI filter fields using the new shared UI components).
    - `src/widgets/OrderTable/OrderTableWidget.vue` (handles tabular list representation and actions).
    - `src/widgets/OrderTable/OrderDetailsModal.vue` (displays details and includes a status modification dropdown).
  - **Page Assembly**: Cleaned up `src/pages/admin/AdminOrders.vue` to act solely as a layout coordinator, importing and composing the new widgets.
- Updated shared components (such as `DataGrid.vue`, `OptimizeVideoModal.vue`, `FeatureLockOverlay.vue`, and `AppTable.vue`) to refer to local relative paths within the `src/shared/ui/` library instead of accessing legacy `/components/` paths, enforcing robust boundary separation.
- Silenced TypeScript compilation warnings and ensured the entire frontend is completely typecheck-clean (`vue-tsc --noEmit` returns exit code 0).

### Phase 6: Public Shopping Cart & Catalog Page Migration
- **Shopping Cart**:
  - Decoupled cart, checkout, and coupon validation logic from `src/pages/cart/ShoppingCart.vue` into a new FSD-compliant composable `src/features/cart/composables/useShoppingCart.ts`.
  - Structured the cart UI into reusable FSD widgets under `src/widgets/ShoppingCart/`:
    - `SuccessMessage.vue` (renders placed order details and success metrics).
    - `CheckoutForm.vue` (manages customer contacts, delivery inputs, and payment methods).
    - `CartItemsList.vue` (renders product items, quantity modifiers, and saved-for-later products).
    - `CartSummary.vue` (displays order subtotals, tax/shipping estimations, promo code apply triggers, and checkout action buttons).
  - Refactored `ShoppingCart.vue` page component to act purely as a coordinator.
- **Catalog Page**:
  - Created FSD composable `src/features/catalog/composables/useCatalog.ts` managing filter states, categories list, brand aggregations, dynamic EAV attributes, pagination, search queries, and quick view modal actions.
  - Added new public catalog endpoints `/v1/catalog/...` to `src/shared/services/api/productApi.ts` for standardized data fetching.
  - Created FSD widgets under `src/widgets/Catalog/`:
    - `CatalogFiltersWidget.vue` (contains category navigation, price sliders, brand selection, and dynamic attributes filters).
    - `PriceRangeSlider.vue` (reusable range selector widget component).
    - `ProductCard.vue` (renders product items in list or grid modes, binding to Pinia stores).
    - `QuickViewModal.vue` (renders detailed specs and add-to-cart controls in a dialog overlay).
  - Updated `src/pages/catalog/CatalogPage.vue` to compose these widgets, ensuring strict type safety and a fully clean build (`vue-tsc --noEmit` passes with exit code 0).

