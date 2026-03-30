# Quick Reference Guide - Multi-Page Website Structure

## 📑 Navigation Routes Quick Reference

```
HOME (Landing Page)
├─ URL: http://localhost:8000/
├─ Route Name: app_home
├─ Template: templates/home/index.html.twig
├─ Controller: src/Controller/HomeController.php
└─ Sections: Hero | Products | About | Bake It Forward | Contact

ABOUT PAGE
├─ URL: http://localhost:8000/about
├─ Route Name: app_about
├─ Template: templates/about/index.html.twig
├─ Controller: src/Controller/AboutController.php
└─ Sections: Hero | Our Story | Meet Team | Why Us | CTA

CONTACT PAGE
├─ URL: http://localhost:8000/contact
├─ Route Name: app_contact
├─ Template: templates/contact/index.html.twig
├─ Controller: src/Controller/ContactController.php
└─ Sections: Hero | Info Boxes | Form | FAQ | CTA

PRODUCTS
├─ URL: http://localhost:8000/product
├─ Route Name: app_product_index
├─ Template: templates/product/index.html.twig
└─ Controller: src/Controller/ProductController.php

LOGIN / REGISTER
├─ URL: http://localhost:8000/login
├─ Route Name: app_login
├─ Template: templates/security/login.html.twig
└─ Controller: src/Controller/LoginController.php
```

---

## 🗂️ File Structure at a Glance

```
CONTROLLERS
src/Controller/
├─ HomeController.php        ← Landing page with 5 sections
├─ AboutController.php       ← About page with team section
├─ ContactController.php     ← Contact page with form processing
├─ ProductController.php
├─ LoginController.php
└─ [other controllers...]

TEMPLATES
templates/
├─ landing.html.twig         ← Public page base (Header + Footer)
├─ home/
│   └─ index.html.twig       ← Landing page content (5 sections)
├─ about/
│   └─ index.html.twig       ← About page (Story + Team + Features)
├─ contact/
│   ├─ index.html.twig       ← Contact page (Info + Form + FAQ)
│   ├─ email_notification.html ← Admin email template
│   └─ email_confirmation.html ← User confirmation email
└─ [other templates...]

STYLES
assets/styles/
├─ app.css                   ← Main stylesheet (updated)
└─ about-contact.css         ← New styles (merged into app.css)

ASSETS
assets/
├─ controllers/
│   ├─ csrf_protection_controller.js
│   ├─ hello_controller.js
│   └─ [other controllers...]
├─ images/
│   ├─ monochrome.png        ← Logo
│   ├─ bakery-hero.jpg       ← Hero background
│   └─ [team & product images...]
└─ vendor/
    └─ @hotwired/            ← Stimulus JS framework
```

---

## 🔗 Using Routes in Templates

### Basic Navigation Links

```twig
{# Link to home #}
<a href="{{ path('app_home') }}">Home</a>

{# Link to about #}
<a href="{{ path('app_about') }}">About Us</a>

{# Link to contact #}
<a href="{{ path('app_contact') }}">Contact</a>

{# Link with anchor (section on page) #}
<a href="{{ path('app_home') }}#bake-it-forward">Bake It Forward</a>

{# Link to products #}
<a href="{{ path('app_product_index') }}">View All Products</a>

{# Conditional links based on auth #}
{% if app.user %}
    <a href="{{ path('app_logout') }}">Logout</a>
{% else %}
    <a href="{{ path('app_login') }}">Login</a>
    <a href="{{ path('app_register') }}">Register</a>
{% endif %}
```

### In Controllers

```php
// Redirect to route
return $this->redirectToRoute('app_home');
return $this->redirectToRoute('app_about');
return $this->redirectToRoute('app_contact');

// Generate URL (for emails, etc)
$url = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
```

---

## 📋 Form Usage Patterns

### Basic Form

```twig
<form method="POST" action="{{ path('app_contact') }}">
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <textarea name="message" required></textarea>
    <button type="submit">Send</button>
</form>
```

### With Error Handling

```twig
{% if formSubmitted and formSuccess %}
    <div class="alert alert-success">
        ✓ Message sent successfully!
    </div>
{% elseif formSubmitted and formError %}
    <div class="alert alert-error">
        ✕ {{ formError }}
    </div>
{% endif %}

<form method="POST">
    {# Form fields here #}
</form>
```

### Server-Side Validation (PHP)

```php
if ($request->isMethod('POST')) {
    $name = trim($request->get('name'));
    $email = trim($request->get('email'));
    
    // Validate
    if (!$name) {
        $error = 'Name is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Process form
    }
}
```

---

## 🎨 Styling Quick Tips

### Using CSS Variables

```css
/* Define once in :root */
:root {
    --primary-color: #8B4513;
    --secondary-color: #F5E6D3;
}

/* Use everywhere */
.button {
    background-color: var(--primary-color);
    color: var(--secondary-color);
}

.card {
    border: 2px solid var(--primary-color);
}
```

### Responsive Design

```css
/* Mobile-first approach */
.container {
    padding: 1rem;  /* Default: small screens */
}

@media (min-width: 768px) {
    .container {
        padding: 2rem;  /* Tablet: more padding */
    }
}

@media (min-width: 1024px) {
    .container {
        max-width: 1200px;  /* Desktop: max width */
        margin: 0 auto;
    }
}
```

### Common Layout Patterns

```css
/* Grid Layout */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

/* Flexbox (horizontal) */
.flex-row {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Flexbox (vertical) */
.flex-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
```

---

## 🧪 Testing Quick Commands

```bash
# Start PHP server
symfony serve

# Access pages
http://localhost:8000/          # Home
http://localhost:8000/about     # About
http://localhost:8000/contact   # Contact

# Check routes
symfony console debug:router

# Clear cache
symfony console cache:clear

# Create database
symfony console doctrine:database:create

# Run migrations
symfony console doctrine:migrations:migrate
```

---

## 🔐 Security Checklist

✅ **Input Validation**
- ✓ Check required fields
- ✓ Validate email format
- ✓ Trim whitespace
- ✓ Check string length

✅ **CSRF Protection**
- ✓ Symfony auto-includes CSRF tokens
- ✓ No additional config needed (in most cases)

✅ **Output Escaping**
- ✓ Twig auto-escapes by default
- ✓ Use `|raw` filter carefully
- ✓ Always validate user input

✅ **Sensitive Data**
- ✓ Don't log passwords
- ✓ Use .env for secrets
- ✓ Never commit .env files
- ✓ Use HTTPS in production

---

## Email Configuration

### Local Testing (MailCatcher)

```env
# .env file
MAILER_DSN=smtp://localhost:1025
```

```bash
# Install MailCatcher
gem install mailcatcher

# Start MailCatcher
mailcatcher

# View emails at
http://localhost:1080
```

### Production (Gmail Example)

```env
# .env.local (production)
MAILER_DSN=smtp://your-email@gmail.com:your-app-password@smtp.gmail.com:587
```

### Production (Brevo Example)

```env
# Using Brevo SMTP
MAILER_DSN=brevo+smtp://your-api-key@default
```

---

## 🆘 Common Issues & Solutions

### Issue: Route Not Found
```
Error: The route "app_about" does not exist.
```
**Solution:**
- Check controller has #[Route('/about', name: 'app_about')]
- Check controller file name (must match namespace)
- Run `symfony console cache:clear`

### Issue: Template Not Found
```
Error: Unable to find template "about/index.html.twig"
```
**Solution:**
- Check template file path (must be in templates/ folder)
- Check file name is exactly "index.html.twig"
- Check symfony knows about template path in config

### Issue: 404 on Form Submission
```
Error: The POST method is not allowed for route "app_contact"
```
**Solution:**
- Add to route: `methods: ['GET', 'POST']`
- Check form method is POST
- Check route pattern matches form action

### Issue: Email Not Sending
```
No errors, but emails don't arrive
```
**Solution:**
- Check MAILER_DSN in .env
- Check email addresses are valid
- Check SMTP credentials are correct
- Look in MailCatcher (if using local testing)
- Check spam folder in email

### Issue: Mobile Menu Not Working
```
Hamburger menu appears but won't open
```
**Solution:**
- Check JavaScript is loading (no console errors)
- Check CSS has display: flex for hamburger
- Check hamburger element has correct id="hamburgerMenu"
- Check nav has correct id="landingNav"

---

## 📱 Mobile Testing

### Breakpoints to Test

```
iPhone SE (375px)        ← Smallest
iPhone 14 (390px)
Galaxy S21 (360px)
iPad Mini (768px)        ← Medium
iPad Pro (1024px+)       ← Large
Desktop (1200px+)
```

### Real Device Testing

```bash
# Find your computer's IP
ipconfig getifaddr en0         # Mac
ipconfig                       # Windows

# Access from mobile device
http://YOUR_IP:8000
```

### Browser DevTools

```
Chrome/Firefox/Safari:
1. Press F12 (or Cmd+Option+I on Mac)
2. Click device icon (top-left)
3. Select device from dropdown
4. Test responsiveness
```

---

## 📊 Performance Metrics to Track

- ⏱️ Page load time (< 3 seconds)
- 🖼️ Image file sizes (< 100KB each)
- 💾 CSS file size (< 100KB)
- ⚡ Core Web Vitals score (90+)
- 📱 Mobile Lighthouse score (90+)

### Measure with Lighthouse

```
Chrome DevTools:
1. Press F12
2. Go to "Lighthouse" tab
3. Click "Analyze page load"
4. View report
```

---

## 🎯 Feature Completion Checklist

```
✅ Landing Page
  ✓ Hero section
  ✓ Featured products
  ✓ About section
  ✓ Bake It Forward
  ✓ Quick contact
  ✓ Responsive design

✅ About Page
  ✓ Hero banner
  ✓ Our story section
  ✓ Team member cards (4+ members)
  ✓ Why choose us section
  ✓ Call-to-action
  ✓ Responsive design

✅ Contact Page
  ✓ Hero banner
  ✓ Contact information
  ✓ Contact form with validation
  ✓ Success/error messages
  ✓ FAQ section
  ✓ Email notifications
  ✓ Responsive design

✅ Navigation
  ✓ Main navigation bar
  ✓ Mobile hamburger menu
  ✓ All links working
  ✓ Active link highlighting

✅ Design & UX
  ✓ Consistent branding
  ✓ Proper spacing
  ✓ Readable typography
  ✓ Accessible forms
  ✓ Mobile-friendly
```

---

## 👨‍💻 Developer Quick Start

### First Time Setup

```bash
# Clone repository
git clone [repo-url]
cd Web_Bakery

# Install dependencies
composer install

# Create .env.local for local settings
cp .env .env.local

# Generate app secret
symfony console secrets:generate-keys

# Setup database
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate

# Start development server
symfony serve

# Access app
http://localhost:8000
```

### Daily Development Workflow

```bash
# Start server
symfony serve

# Watch for changes
symfony console debug:router          # View all routes
symfony console debug:config          # View configuration

# Test form submission
# 1. Go to http://localhost:8000/contact
# 2. Fill out form
# 3. Submit
# 4. Check MailCatcher at http://localhost:1080

# Deploy changes
git add .
git commit -m "Feature: [description]"
git push
```

---

## 📞 Support URLs

- **Documentation**: Check REFACTORING_DOCUMENTATION.md
- **Architecture**: Check ARCHITECTURE_DIAGRAMS.md
- **Code Examples**: Check CODE_EXAMPLES_BEST_PRACTICES.md
- **MVC Pattern**: https://symfony.com/doc/current/
- **Twig Template**: https://twig.symfony.com/
