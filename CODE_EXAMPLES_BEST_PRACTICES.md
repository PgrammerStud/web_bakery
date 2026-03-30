# Code Examples & Best Practices Guide

## 📝 Controller Examples

### 1. Home Controller (Landing Page)

**File:** `src/Controller/HomeController.php`

```php
<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\BakeitforwardwalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        // Fetch featured products (limit to 6)
        $featuredProducts = $productRepository->findBy(
            [],
            ['created_at' => 'DESC'],
            6
        );

        // Fetch donation wallet data
        $wallet = $walletRepository->findOneBy([]);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'wallet' => $wallet,
        ]);
    }
}
```

**Best Practices:**
- ✅ Use dependency injection (ProductRepository, BakeitforwardwalletRepository)
- ✅ Limit queries (findBy with limits)
- ✅ Return Response object
- ✅ Use #[Route] attribute (modern Symfony)
- ✅ Final controller class (prevents accidental inheritance)

---

### 2. About Controller

**File:** `src/Controller/AboutController.php`

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function index(): Response
    {
        // Team data (can move to database later)
        $teamMembers = [
            [
                'name' => 'Maria Santos',
                'position' => 'Head Baker & Founder',
                'bio' => 'With over 20 years of baking experience...',
                'image' => 'team-1.jpg'
            ],
            [
                'name' => 'Juan Cruz',
                'position' => 'Master Baker',
                'bio' => 'Expert in artisan bread and pastries...',
                'image' => 'team-2.jpg'
            ],
            // ... more team members
        ];

        return $this->render('about/index.html.twig', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
```

**Future Enhancement:**
```php
// Move team data to database
#[Route('/about', name: 'app_about')]
public function index(TeamRepository $teamRepository): Response
{
    $teamMembers = $teamRepository->findAll();
    
    return $this->render('about/index.html.twig', [
        'teamMembers' => $teamMembers,
    ]);
}
```

---

### 3. Contact Controller (Form Processing)

**File:** `src/Controller/ContactController.php`

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $formSubmitted = false;
        $formSuccess = false;
        $formError = null;

        if ($request->isMethod('POST')) {
            // Get form data
            $name = trim($request->get('name'));
            $email = trim($request->get('email'));
            $subject = trim($request->get('subject'));
            $message = trim($request->get('message'));
            $phone = trim($request->get('phone'));

            // Validate inputs
            if (!$name || !$email || !$subject || !$message) {
                $formError = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $formError = 'Please enter a valid email address.';
            } else {
                try {
                    // Send email to admin
                    $adminEmail = new Email();
                    $adminEmail
                        ->from('noreply@catalbas-bakery.com')
                        ->to('contact@catalbas.com')
                        ->subject('New Contact Form: ' . $subject)
                        ->html($this->renderView('contact/email_notification.html.twig', [
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone,
                            'subject' => $subject,
                            'message' => $message,
                        ]));

                    $mailer->send($adminEmail);

                    // Send confirmation email to user
                    $userEmail = new Email();
                    $userEmail
                        ->from('noreply@catalbas-bakery.com')
                        ->to($email)
                        ->subject('We Received Your Message - Catalbas Bakery')
                        ->html($this->renderView('contact/email_confirmation.html.twig', [
                            'name' => $name,
                        ]));

                    $mailer->send($userEmail);

                    $formSuccess = true;
                    $formSubmitted = true;
                } catch (\Exception $e) {
                    $formError = 'There was an error sending your message. Please try again later.';
                }
            }

            if ($formError) {
                $formSubmitted = true;
            }
        }

        return $this->render('contact/index.html.twig', [
            'formSubmitted' => $formSubmitted,
            'formSuccess' => $formSuccess,
            'formError' => $formError,
        ]);
    }
}
```

**Security Best Practices:**
- ✅ Trim whitespace from inputs
- ✅ Validate email format
- ✅ Check for required fields
- ✅ Use try-catch for exceptions
- ✅ Don't expose sensitive errors to user
- ✅ Use CSRF tokens (Symfony auto-handles in forms)

---

## 🎨 Template Examples

### 1. Navigation Bar (Reusable)

**File:** `templates/landing.html.twig` (Lines 25-45)

```twig
<nav class="landing-nav" id="landingNav">
    {# Main navigation links #}
    <a href="{{ path('app_home') }}" class="nav-link">Home</a>
    <a href="{{ path('app_product_index') }}" class="nav-link">Products</a>
    <a href="{{ path('app_about') }}" class="nav-link">About</a>
    <a href="{{ path('app_home') }}#bake-it-forward" class="nav-link">Bake It Forward</a>
    <a href="{{ path('app_contact') }}" class="nav-link">Contact</a>
    
    {# Authentication links #}
    {% if app.user %}
        <a href="{{ path('app_logout') }}" class="nav-link nav-logout">Logout</a>
    {% else %}
        <a href="{{ path('app_login') }}" class="nav-link nav-login">Login</a>
        <a href="{{ path('app_register') }}" class="nav-link nav-register">Register</a>
    {% endif %}
</nav>
```

**Template Functions Used:**
- `path()` - Generate URL from route name
- `app.user` - Get current user object
- `if/else` - Conditional rendering

---

### 2. Team Section Component

**File:** `templates/about/index.html.twig` (Team Grid)

```twig
<!-- MEET THE TEAM SECTION -->
<section class="meet-the-team" id="meet-the-team">
    <div class="container">
        <h2>Meet The Team</h2>
        <p class="section-subtitle">The Skilled Hands Behind Every Delicious Bite</p>
        
        <div class="team-grid">
            {% for member in teamMembers %}
                <div class="team-member-card">
                    <div class="team-member-image">
                        {% if member.image %}
                            <img 
                                src="{{ asset('image/' ~ member.image) }}" 
                                alt="{{ member.name }}"
                                onerror="this.src='data:image/svg+xml,%3Csvg ...'">
                        {% else %}
                            <div class="team-placeholder">👤</div>
                        {% endif %}
                    </div>
                    <div class="team-member-info">
                        <h3>{{ member.name }}</h3>
                        <p class="team-position">{{ member.position }}</p>
                        <p class="team-bio">{{ member.bio }}</p>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
</section>
```

**Twig Features Used:**
- `for loop` - Iterate over team members
- `if/else` - Check for image
- `asset()` - Generate asset path
- `~ concatenation` - String operations

---

### 3. Contact Form with Feedback

**File:** `templates/contact/index.html.twig` (Form Section)

```twig
<section class="contact-form-section" id="contact-form-section">
    <div class="container">
        <h2>Send Us a Message</h2>
        <p class="section-subtitle">We'll respond within 24 hours</p>
        
        <div class="contact-form-container">
            {# Success alert #}
            {% if formSubmitted and formSuccess %}
                <div class="alert alert-success" role="alert">
                    <div class="alert-icon">✓</div>
                    <div class="alert-content">
                        <h3>Message Sent Successfully!</h3>
                        <p>Thank you for contacting us. We'll get back to you shortly.</p>
                    </div>
                </div>
            {% endif %}
            
            {# Error alert #}
            {% if formSubmitted and not formSuccess %}
                <div class="alert alert-error" role="alert">
                    <div class="alert-icon">✕</div>
                    <div class="alert-content">
                        <h3>Oops! Something went wrong</h3>
                        <p>{{ formError }}</p>
                    </div>
                </div>
            {% endif %}

            {# Contact form #}
            <form method="POST" class="contact-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact-name">Full Name *</label>
                        <input 
                            type="text" 
                            id="contact-name" 
                            name="name" 
                            required 
                            placeholder="Your Full Name"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contact-phone">Phone Number</label>
                        <input 
                            type="tel" 
                            id="contact-phone" 
                            name="phone" 
                            placeholder="Your Phone Number"
                            class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="contact-email">Email Address *</label>
                    <input 
                        type="email" 
                        id="contact-email" 
                        name="email" 
                        required 
                        placeholder="your@email.com"
                        class="form-control">
                </div>

                <div class="form-group">
                    <label for="contact-subject">Subject *</label>
                    <select id="contact-subject" name="subject" required class="form-control">
                        <option value="">-- Select a Subject --</option>
                        <option value="General Inquiry">General Inquiry</option>
                        <option value="Custom Order">Custom Order</option>
                        <option value="Bulk Order">Bulk Order</option>
                        <option value="Feedback">Feedback</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contact-message">Message *</label>
                    <textarea 
                        id="contact-message" 
                        name="message" 
                        rows="6" 
                        required 
                        placeholder="Tell us how we can help..."
                        class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>
```

**Form Best Practices:**
- ✅ Proper label/input associations (for/id)
- ✅ Required field indicators (*)
- ✅ Helpful placeholders
- ✅ Accessible form structure
- ✅ Clear button text
- ✅ Feedback messages (success/error)

---

### 4. Expandable FAQ Section

**File:** `templates/contact/index.html.twig` (FAQ Section)

```twig
<!-- FAQ SECTION -->
<section class="contact-faq" id="contact-faq">
    <div class="container">
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How fast can I get a custom cake?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>For custom orders, we typically need 3-5 business days. However, we can accommodate rush orders (+₱200) if ordered at least 24 hours in advance.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you offer vegan or gluten-free options?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes! We have a selection of vegan and gluten-free baked goods. Please mention your dietary requirements when ordering.</p>
                </div>
            </div>

            {# Load more FAQ items as needed #}
        </div>
    </div>
</section>

<script>
// FAQ Toggle functionality
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', function() {
        const faqItem = this.parentElement;
        const isActive = faqItem.classList.contains('active');
        
        // Remove active class from all items
        document.querySelectorAll('.faq-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked item
        if (!isActive) {
            faqItem.classList.add('active');
        }
    });
});
</script>
```

**JavaScript Best Practices:**
- ✅ Use querySelectorAll for multiple elements
- ✅ Proper event listeners
- ✅ Toggle active state
- ✅ Only allow one item open at a time
- ✅ Accessible keyboard keys (could add Enter support)

---

## 🎯 CSS Best Practices

### 1. CSS Variables (Root Styles)

**File:** `assets/styles/app.css` (Lines 8-30)

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
    
    /* Shadows */
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.15);
    
    /* Transitions */
    --transition: all 0.3s ease;
}
```

**Benefits:**
- ✅ Easy color updates (change in one place)
- ✅ Consistent spacing and shadows
- ✅ Faster development
- ✅ Better maintainability

### 2. Responsive Grid System

```css
/* Desktop (base styles) */
.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

/* Tablet */
@media (max-width: 768px) {
    .team-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
}

/* Mobile */
@media (max-width: 480px) {
    .team-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
```

**Benefits:**
- ✅ Automatic column adjustment
- ✅ Consistent gaps
- ✅ Mobile-first approach
- ✅ No manual breakpoints needed

### 3. Component Styling (BEM-like)

```css
/* Main component */
.team-member-card {
    background-color: var(--white);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
}

/* Component state */
.team-member-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

/* Sub-component */
.team-member-image {
    width: 100%;
    height: 300px;
    background: linear-gradient(135deg, var(--primary-brown) 0%, var(--primary-wheat) 100%);
}

/* Sub-component styling */
.team-member-info {
    padding: 2rem;
}

.team-position {
    color: var(--accent-gold);
    font-weight: 700;
    text-transform: uppercase;
}
```

**Benefits:**
- ✅ Modular and reusable
- ✅ Easy to maintain
- ✅ Clear component hierarchy
- ✅ Predictable styling

---

## 📍 Accessibility Best Practices

### 1. Semantic HTML

```html
<!-- Good ✅ -->
<nav class="landing-nav" aria-label="Main navigation">
    <a href="/" aria-current="page">Home</a>
    <a href="/about">About</a>
</nav>

<!-- Bad ❌ -->
<div class="nav">
    <span onclick="location='/'">Home</span>
</div>
```

### 2. Form Labels

```html
<!-- Good ✅ -->
<label for="contact-email">Email Address *</label>
<input type="email" id="contact-email" name="email" required>

<!-- Bad ❌ -->
<input type="email" placeholder="Email">
```

### 3. Image Alt Text

```html
<!-- Good ✅ -->
<img src="team-photo.jpg" alt="Maria Santos, Head Baker at Catalbas Bakery">

<!-- Bad ❌ -->
<img src="team-photo.jpg" alt="photo">
```

### 4. Color Contrast

```css
/* Good ✅ - WCAG AA compliant */
.nav-link {
    background-color: #8B4513;  /* Brown */
    color: #FFFFFF;             /* White - High contrast */
}

/* Bad ❌ - Poor contrast */
.nav-link {
    background-color: #F5E6D3;  /* Cream */
    color: #FFF8E7;             /* Light cream - Hard to read */
}
```

---

## 🔄 Routing Best Practices

### 1. Named Routes Pattern

```php
// Always use route names, never hardcode URLs

// Good ✅
<a href="{{ path('app_home') }}">Home</a>
<a href="{{ path('app_about') }}">About</a>
<a href="{{ path('app_contact') }}">Contact</a>

// Bad ❌
<a href="/">Home</a>
<a href="/about">About</a>
<a href="/contact">Contact</a>

// Benefit: If URL changes, all links update automatically
```

### 2. RESTful Route Naming

```php
// Pattern for resource routes:
// Index: /resource (GET) - view all
// Create: /resource/new (GET) - show form
// Store: /resource (POST) - save to DB
// Show: /resource/{id} (GET) - view one
// Edit: /resource/{id}/edit (GET) - edit form
// Update: /resource/{id} (PUT) - update DB
// Delete: /resource/{id} (DELETE) - remove

#[Route('/product', name: 'app_product_index', methods: ['GET'])]
public function index() { ... }

#[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
public function new() { ... }

#[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
public function show(Product $product) { ... }
```

---

## 🧪 Testing Checklist

```
□ Navigation Links
  □ All links clickable
  □ Links navigate to correct pages
  □ Mobile menu opens/closes
  □ Active link highlighting works

□ Form Submission
  □ Form validates required fields
  □ Email validation works
  □ Success message displays
  □ Error message displays
  □ Emails sent to both admin and user
  □ Form clears after success

□ Responsive Design
  □ 480px (mobile)
  □ 768px (tablet)
  □ 1024px (desktop)
  □ Images scale properly
  □ Text readable on all sizes
  □ Hamburger menu works on mobile

□ Accessibility
  □ Keyboard navigation (Tab key)
  □ Screen reader compatibility
  □ Color contrast sufficient
  □ Form labels present
  □ Alt text on images

□ Browser Compatibility
  □ Chrome (latest)
  □ Firefox (latest)
  □ Safari (latest)
  □ Edge (latest)
  □ Mobile Safari (iOS)
  □ Chrome Mobile (Android)
```

---

## 🚀 Performance Tips

### 1. Image Optimization

```html
<!-- Bad ❌ - Large file, no formats -->
<img src="bakery.jpg" alt="Bakery">

<!-- Good ✅ - Multiple formats, responsive -->
<picture>
    <source srcset="bakery.webp" type="image/webp">
    <source srcset="bakery.jpg" type="image/jpeg">
    <img src="bakery.jpg" alt="Catalbas Bakery" loading="lazy">
</picture>
```

### 2. CSS Optimization

```css
/* Avoid ❌ */
.nav-link {
    color: red;
}
.nav-link:hover {
    color: red;
}

/* Better ✅ */
.nav-link:hover {
    color: var(--primary-brown);
}
```

### 3. JavaScript Optimization

```js
/* Avoid ❌ - Repeated DOM queries */
for (let i = 0; i < 1000; i++) {
    document.querySelector('.nav-link').addEventListener('click', ...);
}

/* Better ✅ - Query once, add event listener once */
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', ...);
});
```

---

## 📚 Additional Resources

### Symfony Documentation
- Routing: https://symfony.com/doc/current/routing.html
- Controllers: https://symfony.com/doc/current/controller.html
- Twig: https://twig.symfony.com/

### Frontend Best Practices
- MDN Web Docs: https://developer.mozilla.org/
- CSS Grid: https://css-tricks.com/snippets/css/complete-guide-grid/
- Accessibility: https://www.w3.org/WAI/

### Tools
- Chrome DevTools - Inspect & debug
- Lighthouse - Performance audits
- Accessibility Checker - Axe or WAVE
