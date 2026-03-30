# CSS Variable Migration Guide

## Old → New Variable Mapping

### Primary Color Variables
| Old Variable | New Variable | Hex Value | Purpose |
|---|---|---|---|
| `--primary-brown` | `--color-primary` (use with variants) | #DD8928 | Brand orange |
| `--secondary-dark` | `--color-secondary` | #3B2416 | Deep brown |
| `--primary-cream` | `--color-bg-soft` | #FEF8F3 | Soft cream background |
| `--primary-wheat` | `--color-bg-light-tan` | #E6D5C3 | Light tan |
| `--accent-gold` | `--color-accent-teal` | #4A8FB8 | Complementary teal |
| `--white` | `--color-bg-white` | #FFFFFF | White |
| `--gray-light` | `--color-bg-gray-light` | #F5F5F5 | Light gray |
| `--gray-medium` | `--color-bg-gray-medium` | #EEEBE5 | Medium gray |
| `--text-dark` | `--color-text-dark` | #2C1810 | Dark text |
| `--secondary-light` | `--color-secondary-light` | #5A4A42 | Brown light |

## Search & Replace Strategy

### Component-by-Component Replacements

#### Header & Navigation
```css
/* OLD */
background: linear-gradient(135deg, var(--primary-brown) 0%, var(--secondary-dark) 100%);
color: var(--white);

/* NEW */
background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
color: var(--color-text-on-dark);
```

#### Buttons (Primary)
```css
/* OLD */
.btn-primary {
    background: linear-gradient(135deg, var(--primary-brown) 0%, var(--secondary-dark) 100%);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--primary-brown);
}

/* NEW */
.btn-primary {
    background: var(--color-primary);
    color: var(--color-text-on-dark);
}

.btn-primary:hover {
    background: var(--color-primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
```

#### Buttons (Secondary)
```css
/* OLD */
.btn-secondary {
    background-color: var(--primary-cream);
    color: var(--primary-brown);
    border: 2px solid var(--primary-brown);
}

.btn-secondary:hover {
    background-color: var(--primary-brown);
    color: var(--white);
}

/* NEW */
.btn-secondary {
    background-color: var(--color-bg-soft);
    color: var(--color-primary);
    border: 2px solid var(--color-primary);
}

.btn-secondary:hover {
    background-color: var(--color-primary);
    color: var(--color-text-on-dark);
    border-color: var(--color-primary-dark);
}
```

#### Forms
```css
/* OLD */
input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary-brown);
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

label {
    color: var(--text-dark);
}

/* NEW */
input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(221, 137, 40, 0.1);
}

label {
    color: var(--color-text-dark);
}
```

#### Headings
```css
/* OLD */
h1, h2, h3, h4, h5, h6 {
    color: var(--primary-brown);
}

/* NEW */
h1, h2, h3, h4, h5, h6 {
    color: var(--color-primary);
}
```

#### Tables
```css
/* OLD */
thead {
    background: linear-gradient(135deg, var(--primary-brown) 0%, var(--secondary-dark) 100%);
    color: var(--white);
}

tbody tr:hover {
    background-color: var(--primary-cream);
}

tbody tr {
    border-bottom: 1px solid var(--gray-medium);
}

/* NEW */
thead {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
    color: var(--color-text-on-dark);
}

tbody tr:hover {
    background-color: var(--color-bg-soft);
}

tbody tr {
    border-bottom: 1px solid var(--color-border-light);
}
```

#### Sidebar
```css
/* OLD */
.sidebar ul li a:hover {
    background-color: var(--primary-cream);
    border-left-color: var(--primary-brown);
}

.sidebar ul li a.active {
    background: linear-gradient(90deg, var(--primary-cream) 0%, var(--secondary-light) 100%);
    border-left-color: var(--primary-brown);
    color: var(--primary-brown);
}

/* NEW */
.sidebar ul li a:hover {
    background-color: var(--color-bg-soft);
    border-left-color: var(--color-primary);
}

.sidebar ul li a.active {
    background: linear-gradient(90deg, var(--color-bg-soft) 0%, var(--color-secondary-light) 100%);
    border-left-color: var(--color-primary);
    color: var(--color-primary);
}
```

#### Cards
```css
/* OLD */
.card {
    background-color: var(--white);
    box-shadow: var(--shadow-sm);
}

.card h2 {
    color: var(--primary-brown);
}

/* NEW */
.card {
    background-color: var(--color-bg-white);
    box-shadow: var(--shadow-sm);
}

.card h2 {
    color: var(--color-primary);
}
```

### Global Search Patterns

Use these patterns to find and replace:

1. `--primary-brown` → `--color-primary`
2. `--secondary-dark` → `--color-secondary`
3. `--primary-cream` → `--color-bg-soft`
4. `--primary-wheat` → `--color-bg-light-tan`
5. `--accent-gold` → `--color-accent-teal`
6. `--white` → `--color-bg-white`
7. `--gray-light` → `--color-bg-gray-light`
8. `--gray-medium` → `--color-bg-gray-medium` or `--color-border-light`
9. `--secondary-light` → `--color-secondary-light`

### Undefined Variables (Need to Add)
These variables are used in CSS but not defined in root:
- `--primary-brown` (should be `--color-primary`)
- `--secondary-dark` (should be `--color-secondary`)
- `--white` (should be `--color-bg-white`)
- `--gray-light` (should be `--color-bg-gray-light`)
- `--gray-medium` (should be `--color-bg-gray-medium`)
- `--text-dark` (should be `--color-text-dark`)

## Files to Update

1. ✅ `assets/styles/app.css` - Main stylesheet (UPDATED - root section has new variables)
2. ⏳ `assets/styles/about-contact.css` - About & Contact pages
3. All HTML templates that use old class names

## Accessibility Testing After Migration

Use these tools to verify contrast ratios:
- WebAIM Contrast Checker: https://webaim.org/resources/contrastchecker/
- Chrome DevTools Accessibility Panel
- Lighthouse Accessibility Audit (DevTools > Lighthouse)

Minimum requirements:
- Normal text: 4.5:1 contrast ratio (WCAG AA)
- Large text (18pt+): 3:1 contrast ratio (WCAG AA)
- Buttons: 3:1 contrast ratio (WCAG AA)

## Priority Components

These should be updated first:
1. ✅ Root CSS variables (COMPLETE)
2. ⏳ Buttons (primary, secondary, danger, warning)
3. ⏳ Footer text (IMPORTANT for contrast)
4. ⏳ Form inputs and focus states
5. ⏳ Links and interactive elements
6. ⏳ Tables
7. ⏳ Cards
8. ⏳ Sidebar
9. ⏳ Header/Navigation
10. ⏳ Alerts and semantic colors

## Testing Checklist

After migration, test:
- [ ] Button colors and hover states
- [ ] Form focus states
- [ ] Footer text readability
- [ ] Link colors and visited states
- [ ] Table alternating row colors
- [ ] Card shadows and borders
- [ ] Alert backgrounds and text
- [ ] Mobile responsive design
- [ ] Accessibility contrast ratios
- [ ] All pages load without errors
