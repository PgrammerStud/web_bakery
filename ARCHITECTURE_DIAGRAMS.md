# Website Architecture & Routing Diagram

## 🗺️ Site Map & Navigation Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    CATALBAS BAKERY WEBSITE                  │
└─────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┼─────────────┐
                │             │             │
           ┌────▼───┐    ┌────▼────┐  ┌────▼──────┐
           │  HOME  │    │  ABOUT  │  │  CONTACT  │
           │   (/)  │    │ (/about)│  │ (/contact)│
           └────┬───┘    └────┬────┘  └────┬──────┘
                │             │             │
        ┌───────┴─────┐   ┌───┴────┐   ┌───┴────────┐
        │             │   │        │   │            │
   ┌────▼────┐  ┌────▼──┐│ ┌──────▼──┐│ ┌─────────▼─┐
   │ PRODUCTS │  │ ABOUT │ │OUR STORY ││ │ FORM      │
   │          │  │BAKERY │ │TEAM     ││ │ INFO      │
   └──────────┘  │FEATURES │         ││ │ FAQ       │
           │     │         │ WHY US  ││ │ CTA       │
       FLOW:     │                   ││ │           │
       1.Hero    └────────────────────┘│ └───────────┘
       2.Products                      │
       3.About                    FLOW: │      FLOW:
       4.Bake It Forward         1.Story│ 1.Contact Info
       5.Quick Contact           2.Team │ 2.Contact Form
                                 3.Why Us│ 3.FAQ
                                 4.CTA   │ 4.CTA
```

## 🔀 User Navigation Paths

```
First-time Visitor Flow:
    ↓
LANDING PAGE (/)
    ├─→ Hero banner (explains What & Why?)
    ├─→ Products section (What do you sell?)
    ├─→ About section (Who are you?)
    ├─→ Bake It Forward (What's your mission?)
    └─→ Contact section (How to reach you?)

Interested Visitor Flow:
    ↓
HOME → ABOUT PAGE
    ├─→ Our Story (Learn history)
    ├─→ Meet the Team (Know the people)
    ├─→ Why Choose Us (See benefits)
    └─→ CTA (View Products or Contact)

Ready to Contact Flow:
    ↓
HOME/ABOUT → CONTACT PAGE
    ├─→ Contact Info (Find hours/location)
    ├─→ Contact Form (Send message)
    ├─→ FAQ (Find answers)
    └─→ Success Feedback (Confirmation)

Authentication Flow:
    ↓
ANYWHERE → LOGIN (/login)
    ├─→ Enter credentials
    └─→ Redirect to Dashboard
```

## 📡 Data Flow Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                     CLIENT (Browser)                           │
│  User requests: / | /about | /contact | /login                │
└────────────────┬─────────────────────────────────────────────┘
                 │ HTTP Request
                 ▼
┌────────────────────────────────────────────────────────────────┐
│                  SYMFONY ROUTING                               │
│  Matches URL pattern to controller method                      │
└────────────────┬─────────────────────────────────────────────┘
                 │
        ┌────────┼────────┬────────┐
        │        │        │        │
    ┌───▼──┐ ┌──▼───┐ ┌──▼────┐ ┌▼───────┐
    │ Home │ │About │ │Contact │ │ Login  │
    │ Ctrl │ │ Ctrl │ │ Ctrl  │ │ Ctrl   │
    └───┬──┘ └──┬───┘ └──┬────┘ └┬───────┘
        │       │       │        │
┌───────▼───────▼───────▼────────▼────────────────────────────────┐
│              DATABASE QUERIES (if needed)                       │
│  - Get featured products (Home)                                │
│  - Get team members data (About)                               │
│  - Save contact form (Contact)                                 │
│  - Validate user credentials (Login)                           │
└───────┬────────────────────────────────────────────────────────┘
        │
┌───────▼──────────────────────────────────────────────────────────┐
│                  RENDER TEMPLATE                                 │
│  - Inject data into Twig templates                              │
│  - Process conditional logic                                    │
│  - Apply CSS styles                                             │
└───────┬──────────────────────────────────────────────────────────┘
        │ HTML Response
        ▼
┌────────────────────────────────────────────────────────────────┐
│                     CLIENT (Browser)                           │
│  Display: Landing/About/Contact/Login Page                    │
│  Execute: JavaScript (hamburger menu, FAQ toggle, etc.)       │
└────────────────────────────────────────────────────────────────┘
```

## 📧 Contact Form Processing Flow

```
User fills form
    ↓
POST to /contact
    ↓
 ┌──────────────────────────┐
 │ ContactController        │
 │ - Validate inputs        │
 │ - Check for errors       │
 └──────────┬───────────────┘
            │
        ┌───▼────┐
        │ Valid? │
        └───┬────┘
            │
    ┌───Yes─┴─No─────┐
    │                │
┌───▼────────┐  ┌────▼──────────┐
│  Send      │  │  Return       │
│  Emails:   │  │  error msg &  │
│  1. Admin  │  │  form data    │
│  2. User   │  └───────────────┘
└───┬────────┘
    │
    ▼
 SUCCESS!
 Show success message
 Clear form fields
```

## 🏗️ Template Inheritance Structure

```
landing.html.twig (Base for public pages)
    ├─ Header
    │  ├─ Logo
    │  └─ Navigation
    │     ├─ Home link
    │     ├─ About link
    │     ├─ Contact link
    │     └─ Auth links (Login/Logout)
    │
    ├─ Main Content (Block: body)
    │  ├─ Alerts (Flash messages)
    │  └─ Page-specific content
    │
    └─ Footer
       ├─ Company info
       ├─ Contact details
       └─ Social links

             │
    ┌────────┼────────┐
    │        │        │
  home/    about/   contact/
index.twig index.twig index.twig

Each extends landing.html.twig
and fills the {% block body %}
```

## 🎯 SEO & Metadata Structure

```
Page Meta Tags:
    ├─ Home (/)
    │  ├─ Title: "Catalbas Bakery - Freshly Baked Every Morning"
    │  ├─ Description: "Premium handmade baked goods with fresh ingredients"
    │  └─ Keywords: "bakery, fresh bread, pastries, Manila"
    │
    ├─ About (/about)
    │  ├─ Title: "About Catalbas Bakery - Our Story & Team"
    │  ├─ Description: "Learn about our bakery history and expert team"
    │  └─ Keywords: "bakery team, baking experience, artisan bread"
    │
    └─ Contact (/contact)
       ├─ Title: "Contact Catalbas Bakery - Get In Touch"
       ├─ Description: "Contact our bakery for orders, inquiries, or feedback"
       └─ Keywords: "contact bakery, ordering, inquiry, customer service"
```

## 🔐 Authentication Integration

```
Public Pages (No Auth Required):
    ├─ Home (/)
    ├─ About (/about)
    ├─ Contact (/contact)
    ├─ Products (/product)
    ├─ Login (/login)
    └─ Register (/register)

Protected Pages (Auth Required):
    ├─ Dashboard (/dashboard) - ROLE_ADMIN only
    ├─ Profile (/profile)
    └─ Order History (/orders)

Role-based Navigation:
    ├─ Anonymous User
    │  ├─ Shows: Login link
    │  └─ Shows: Register link
    │
    └─ Authenticated User
       ├─ Shows: Profile link
       ├─ Shows: Logout link
       └─ Shows: Dashboard (if admin)
```

## 📊 Component Hierarchy

```
Landing Page Components:

HeroSection (Hero-type)
├─ Background Image
├─ Overlay
├─ Content
│  ├─ Headline (h1)
│  ├─ Subtitle (p)
│  └─ Buttons (CTA)
└─ CSS Class: .hero

ProductGrid (Multi-column)
├─ ProductCard (Repeating)
│  ├─ Image
│  ├─ Title
│  ├─ Description
│  ├─ Price
│  └─ Button
└─ CSS Class: .products-grid

InfoBoxes (Feature boxes)
├─ Icon/Emoji
├─ Title
├─ Description
└─ CSS Class: .info-block

DonationCard (Progress indicator)
├─ Icon/Emoji
├─ Progress bar
├─ Stats (Raised / Goal)
└─ Message

ContactForm (Form inputs)
├─ TextInputs (Name, Email, Phone)
├─ SelectDropdown (Subject)
├─ TextArea (Message)
├─ Checkbox (Newsletter)
└─ SubmitButton
```

## 🎨 Breakpoint Hierarchy

```
Desktop-First vs Mobile-First Architecture:

IMPLEMENTED: Mobile-First (Recommended)

Base Styles (320px - all devices)
    ↓
@media (max-width: 768px)
    └─ Tablet adjustments
        └─ Smaller grids
        └─ Reduced padding
        └─ Hamburger menu
    ↓
@media (max-width: 480px)
    └─ Mobile adjustments
        └─ Single column
        └─ Touch-friendly buttons
        └─ Larger text

Result:
    ├─ Desktop: 100% width, multi-column
    ├─ Tablet: 85% width, 2 columns
    └─ Mobile: 95% width, 1 column
```

## 🔗 Route Registration Process

```
1. Controller Created/Updated
   └─ #[Route('/about', name: 'app_about')]
      public function index(): Response

2. Symfony Auto-discovers Route
   (via config/routes.yaml)

3. Route Available Globally
   └─ In templates: {{ path('app_about') }}
   └─ In controllers: $this->redirectToRoute('app_about')

4. User navigates to /about
   └─ Router matches pattern
   └─ Executes controller method
   └─ Returns rendered template
```

## 📈 Performance Considerations

```
Current Optimizations:
    ├─ CSS minification (single file)
    ├─ Asset versioning (cache busting)
    ├─ Responsive images (lazy loading ready)
    ├─ Efficient queries (findBy limits)
    └─ No unused CSS

Recommended Additions:
    ├─ Image compression (WebP format)
    ├─ CSS/JS minification in production
    ├─ Browser caching headers
    ├─ Gzip compression
    ├─ CDN for static assets
    └─ Database query optimization
```
