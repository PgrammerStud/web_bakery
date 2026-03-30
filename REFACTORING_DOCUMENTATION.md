# Catalbas Bakery - Multi-Page Website Refactoring Documentation

## 📋 Project Structure Overview

### Updated File Structure

```
Web_Bakery/
├── src/
│   └── Controller/
│       ├── HomeController.php          ← Landing Page (5 sections)
│       ├── AboutController.php         ← NEW: About Page
│       ├── ContactController.php       ← NEW: Contact Page
│       ├── BakeItForwardController.php ← Optional: Donation Page (currently in Home)
│       ├── ProductController.php       ← Product Listings
│       └── [other controllers...]
│
├── templates/
│   ├── base.html.twig                  ← Admin base layout
│   ├── landing.html.twig               ← Landing page base layout (Header + Footer)
│   ├── home/
│   │   └── index.html.twig             ← Landing Page (HOME ROUTE: /)
│   ├── about/
│   │   └── index.html.twig             ← About Page (ABOUT ROUTE: /about)
│   ├── contact/
│   │   ├── index.html.twig             ← Contact Page (CONTACT ROUTE: /contact)
│   │   ├── email_notification.html     ← Admin email template
│   │   └── email_confirmation.html     ← User confirmation email
│   └── [other templates...]
│
├── assets/
│   ├── styles/
│   │   ├── app.css                     ← Main stylesheet (updated with new styles)
│   │   └── about-contact.css           ← New styles for About & Contact
│   ├── controllers/
│   │   └── [JavaScript controllers]
│   └── images/
│       └── [bakery images]
│
└── config/
    └── routes.yaml                     ← Routes configuration (auto-loaded)
```

---

## 🛣️ Routes & URL Structure

### Available Routes

| Route | Controller | Template | Purpose |
|-------|-----------|----------|---------|
| `/` | `HomeController::index()` | `home/index.html.twig` | **Landing Page** - Hero, Products, About, Bake It Forward, Contact |
| `/about` | `AboutController::index()` | `about/index.html.twig` | **About Page** - Our Story, Meet the Team, Why Choose Us, CTA |
| `/contact` | `ContactController::index()` | `contact/index.html.twig` | **Contact Page** - Contact Info, Form, FAQ, CTA |
| `/login` | `LoginController::login()` | `security/login.html.twig` | **Login Page** - User authentication |
| `/register` | `RegistrationController::register()` | `registration/register.html.twig` | **Register Page** - New user signup |
| `/product` | `ProductController::index()` | `product/index.html.twig` | **Products Listing** - All products |
| `/bake-it-forward` | *(Optional)* | *(Currently in Home)* | Donation program (can be standalone) |

### Routing Configuration

Routes are **automatically detected** in Symfony via the `#[Route]` attribute on controller methods:

```yaml
# config/routes.yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
```

---

## 🎨 Navigation Bar Code

### Main Navigation Template
**Location:** `templates/landing.html.twig` (Lines 25-45)

```twig
<nav class="landing-nav" id="landingNav">
    <a href="{{ path('app_home') }}" class="nav-link">Home</a>
    <a href="{{ path('app_product_index') }}" class="nav-link">Products</a>
    <a href="{{ path('app_about') }}" class="nav-link">About</a>
    <a href="{{ path('app_home') }}#bake-it-forward" class="nav-link">Bake It Forward</a>
    <a href="{{ path('app_contact') }}" class="nav-link">Contact</a>
    {% if app.user %}
        <a href="{{ path('app_logout') }}" class="nav-link nav-logout">Logout</a>
    {% else %}
        <a href="{{ path('app_login') }}" class="nav-link nav-login">Login</a>
        <a href="{{ path('app_register') }}" class="nav-link nav-register">Register</a>
    {% endif %}
</nav>
```

### Navigation Styles
**Location:** `assets/styles/app.css` (Lines 2720-2810)

```css
/* Landing Navigation */
.landing-nav {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.nav-link {
    color: var(--white);
    text-decoration: none;
    padding: 0.75rem 1.25rem;
    border-radius: 6px;
    transition: var(--transition);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.nav-link:hover,
.nav-link.active {
    background-color: var(--primary-cream);
    color: var(--primary-brown);
}

/* Mobile Hamburger Menu */
.hamburger-menu {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    gap: 5px;
}

.hamburger-menu span {
    width: 25px;
    height: 3px;
    background-color: var(--white);
    border-radius: 3px;
    transition: var(--transition);
}

/* Responsive - Mobile Menu */
@media (max-width: 768px) {
    .landing-nav {
        position: absolute;
        top: 70px;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, var(--primary-brown) 0%, var(--secondary-dark) 100%);
        flex-direction: column;
        gap: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .landing-nav.active {
        max-height: 500px;
        box-shadow: var(--shadow-lg);
    }

    .hamburger-menu {
        display: flex;
    }
}
```

---

## 📄 Page Components & Architecture

### 1. Landing Page (Home)

**File:** `templates/home/index.html.twig`

**5 Main Sections:**

```
┌─────────────────────────────────────┐
│   1. HERO SECTION                   │
│   - Background image               │
│   - Main headline                 │
│   - Call-to-action buttons        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   2. FEATURED PRODUCTS              │
│   - Product grid (3-4 columns)     │
│   - Product cards with images      │
│   - Price and order buttons        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   3. ABOUT THE BAKERY               │
│   - Company description            │
│   - 3 highlight boxes (Features)   │
│   - About image                    │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   4. BAKE IT FORWARD                │
│   - Donation progress bar          │
│   - Goal and raised amount         │
│   - Mission statement              │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   5. CONTACT US (Quick Contact)     │
│   - Contact info cards             │
│   - Quick contact form             │
│   - Business hours                 │
└─────────────────────────────────────┘
```

**Controller Code:**
```php
#[Route('/', name: 'app_home')]
public function index(
    ProductRepository $productRepository,
    BakeitforwardwalletRepository $walletRepository
): Response {
    return $this->render('home/index.html.twig', [
        'featuredProducts' => $productRepository->findBy([], ['created_at' => 'DESC'], 6),
        'wallet' => $walletRepository->findOneBy([]),
    ]);
}
```

---

### 2. About Page

**File:** `templates/about/index.html.twig`
**Controller:** `src/Controller/AboutController.php`

**4 Main Sections:**

```
┌─────────────────────────────────────┐
│   HERO BANNER                       │
│   "About Catalbas Bakery"          │
│   Quick tagline                    │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   OUR STORY                         │
│   - Company narrative              │
│   - 3 story cards (Roots, Values)  │
│   - Mission statement              │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   MEET THE TEAM                     │
│   - 4 team member cards            │
│   - Images, names, positions       │
│   - Short bios                     │
│   - Hover effects                  │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   WHY CHOOSE US                     │
│   - 6 feature boxes                │
│   - Icons, titles, descriptions    │
│   - Responsive grid                │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   CALL-TO-ACTION                    │
│   - Links to Products & Contact    │
└─────────────────────────────────────┘
```

**Team Data Structure:**
```php
$teamMembers = [
    [
        'name' => 'Maria Santos',
        'position' => 'Head Baker & Founder',
        'bio' => 'With over 20 years of baking experience...',
        'image' => 'team-1.jpg'
    ],
    // ... more team members
];
```

---

### 3. Contact Page

**File:** `templates/contact/index.html.twig`
**Controller:** `src/Controller/ContactController.php`

**5 Main Sections:**

```
┌─────────────────────────────────────┐
│   HERO BANNER                       │
│   "Contact Us"                      │
│   "We'd Love to Hear From You"     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   CONTACT INFORMATION               │
│   - 6 info boxes                   │
│   - Address, Phone, Email          │
│   - Hours, Delivery, Social Media   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   CONTACT FORM                      │
│   - Name, Email, Phone, Subject    │
│   - Message textarea               │
│   - Newsletter checkbox            │
│   - Success/Error alerts           │
│   - Server-side validation         │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   FAQ SECTION                       │
│   - 5 expandable questions         │
│   - Toggle functionality           │
│   - Mobile-friendly               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│   CALL-TO-ACTION                    │
│   - View Products & Meet Team      │
└─────────────────────────────────────┘
```

**Form Processing:**
```php
#[Route('/contact', name: 'app_contact')]
public function index(Request $request, MailerInterface $mailer): Response {
    if ($request->isMethod('POST')) {
        // Validate inputs
        // Send admin notification email
        // Send user confirmation email
        // Return success/error response
    }
    return $this->render('contact/index.html.twig', [...]);
}
```

**Email Integration:**
- ✅ Admin notification email
- ✅ User confirmation email
- ✅ Server-side validation
- ✅ User-friendly feedback messages

---

## 🎯 Recommendation: Bake It Forward Structure

### Current Structure (Recommended)
**KEEP on Landing Page** - It's a core brand message

✅ **Pros:**
- Builds awareness on first visit
- Integrates with hero/call-to-action flow
- Shows company values immediately
- Visitors see donation progress at entry point

### Alternative: Standalone Page
If you want more detail, create `/bake-it-forward`:

```php
#[Route('/bake-it-forward', name: 'app_bake_it_forward')]
public function bakeitforward(): Response {
    return $this->render('bakeitforward/index.html.twig', [...]);
}
```

**When to use:**
- If you want detailed donation history
- Testimonials from shelters
- Donor recognition section
- Detailed impact reports

❌ **Recommendation:** Keep on Landing Page (current setup is better)

---

## 🎨 Design & Styling System

### Color Palette
Located in `assets/styles/app.css` (Lines 8-24):

```css
:root {
    /* Primary Colors - Warm bakery palette */
    --primary-brown: #8B4513;        /* Main brand color */
    --primary-cream: #F5E6D3;        /* Accent/backgrounds */
    --primary-wheat: #D4A574;        /* Highlights */
    
    /* Secondary Colors */
    --secondary-dark: #5C3317;       /* Gradients */
    --secondary-light: #FFF8E7;      /* Light backgrounds */
    --accent-gold: #C9A961;          /* Premium feel */
    
    /* Neutral Colors */
    --white: #FFFFFF;
    --gray-light: #F5F5F5;
    --gray-medium: #CCCCCC;
    --gray-dark: #666666;
    --text-dark: #333333;
}
```

### Responsive Breakpoints

✅ **3 Mobile-First Breakpoints:**

```css
/* Mobile (320px - 479px)  */
@media (max-width: 480px) { ... }

/* Tablet (480px - 767px)  */
@media (max-width: 768px) { ... }

/* Desktop (768px+)        */
/* Base styles */
```

---

## 📱 Mobile Responsiveness

### Features Implemented

✅ Hamburger menu for mobile navigation
✅ Responsive grid layouts (auto-fit, minmax)
✅ Touch-friendly buttons (min 44px height)
✅ Readable text on all screen sizes
✅ Optimized images and spacing
✅ Flexible forms with proper layout
✅ Collapsible sections (FAQ)

### Mobile Testing Checklist

```
□ Test on iPhone SE (375px)
□ Test on iPhone 12 (390px)
□ Test on iPhone 14 Pro Max (430px)
□ Test on Samsung Galaxy S21 (360px)
□ Test on iPad Mini (768px)
□ Test on iPad Pro (1024px)
□ Test on Chrome DevTools (all breakpoints)
```

---

## 🔧 Technical Implementation Details

### File Includes & Processing

**1. Navigation Flow:**
```
landing.html.twig (Base template)
├── Header with logo & nav
├── Main content (extends to home/about/contact)
├── Footer
└── JavaScript (smooth scroll, hamburger menu)
```

**2. Form Submission Flow:**
```
Contact Form (POST)
    ↓
ContactController->index()
    ↓
Validate inputs
    ↓
Send emails (Admin + User)
    ↓
Return response (Success/Error)
    ↓
Display alert on same page
```

**3. Email Configuration:**

Add to `.env`:
```env
# For local testing (MailCatcher)
MAILER_DSN=smtp://localhost:1025

# For production (example with Brevo)
MAILER_DSN=brevo+smtp://user:password@default
```

---

## 💡 UX/Layout Improvement Suggestions

### ✅ Already Implemented
- Smooth scrolling navigation links
- Hamburger menu for mobile
- Sticky header
- Clear visual hierarchy
- Hover effects on interactive elements
- Loading states and feedback messages
- Fast-loading lazy images

### 🚀 Quick Wins (Easy to Add)

**1. Add a Search Bar**
```twig
<div class="search-bar">
    <input type="search" placeholder="Search products...">
    <button type="submit">Search</button>
</div>
```

**2. Add Breadcrumb Navigation**
```twig
{# For About page #}
<nav class="breadcrumb">
    <a href="{{ path('app_home') }}">Home</a> > About Us
</nav>
```

**3. Add "Back to Top" Button**
```html
<button id="backToTop" class="back-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
    ↑ Top
</button>
```

**4. Add Social Media Icons**
```twig
<div class="social-links">
    <a href="#"><i class="fab fa-facebook"></i></a>
    <a href="#"><i class="fab fa-instagram"></i></a>
    <a href="#"><i class="fab fa-twitter"></i></a>
</div>
```

**5. Add View Transitions**
```css
@supports (view-transition-name: auto) {
    @view-transition {
        navigation: auto;
    }
}
```

### 🎯 Medium Complexity (Enhance Experience)

**1. Add Analytics Tracking**
```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>
```

**2. Add WhatsApp Chat Widget**
- Quick customer support
- Easy order placement

**3. Add Product Filters**
- By category, price, rating
- Improves discoverability

**4. Testimonials Section**
- Customer reviews on landing page
- Build trust with new visitors

**5. Email Newsletter Signup**
- Collect emails for marketing
- Add to footer or sidebar

### 🏆 Advanced (Next Phase)

**1. E-commerce Integration**
- Shopping cart
- Payment gateway (Stripe/PayPal)
- Order tracking

**2. Admin Dashboard**
- Manage products
- View contact submissions
- Sales analytics

**3. Blog Section**
- Baking tips & recipes
- Company news
- SEO benefits

**4. User Accounts**
- Order history
- Saved preferences
- Loyalty rewards

---

## 📊 File Organization Summary

### Total New/Modified Files

| File | Type | Status | Lines |
|------|------|--------|-------|
| `src/Controller/AboutController.php` | Controller | ✅ Created | 45 |
| `src/Controller/ContactController.php` | Controller | ✅ Created | 60 |
| `templates/about/index.html.twig` | Template | ✅ Created | 140 |
| `templates/contact/index.html.twig` | Template | ✅ Created | 200 |
| `templates/contact/email_notification.html` | Email | ✅ Created | 60 |
| `templates/contact/email_confirmation.html` | Email | ✅ Created | 65 |
| `templates/landing.html.twig` | Template | ✅ Updated | Navigation links |
| `assets/styles/app.css` | Styles | ✅ Updated | +600 lines |

**Total:** ~1,170 lines of new/updated code

---

## ✅ Compliance Checklist

### Academic Project Requirements

- ✅ **Multi-page structure** (Home, About, Contact)
- ✅ **Landing page with 5+ sections** (Hero, Products, About, Bake It Forward, Contact)
- ✅ **About page with team section** (4 team members with names & roles)
- ✅ **Contact page with form** (Validation, Success/Error feedback)
- ✅ **Navigation bar** (Links to all main pages)
- ✅ **Consistent branding** (Colors, fonts, styling)
- ✅ **Responsive design** (Mobile, tablet, desktop)
- ✅ **No styles removed** (Reused existing CSS, added new sections)
- ✅ **Email integration** (Admin + User notifications)
- ✅ **Accessibility basics** (Proper HTML, labels, alt text)

---

## 🚀 Next Steps to Deploy

```bash
# 1. Install dependencies (if needed)
composer install

# 2. Create database migration (if using email features)
symfony console doctrine:migrations:create

# 3. Test locally
symfony serve

# 4. Access pages:
#    http://localhost:8000/                 (Home)
#    http://localhost:8000/about            (About)
#    http://localhost:8000/contact          (Contact)

# 5. Deploy to production
git add .
git commit -m "Refactor: Multi-page website structure"
git push
```

---

## 📞 Support

For any questions about:
- **Routes:** Check `src/Controller/` files
- **Templates:** Check `templates/` directories  
- **Styles:** Check `assets/styles/app.css`
- **Forms:** Check `ContactController.php` for validation logic
- **Emails:** Check `templates/contact/` email templates
