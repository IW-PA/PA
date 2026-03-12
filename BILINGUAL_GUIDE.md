# Bilingual System - Usage Guide

## Overview
Budgie now supports **French (FR)** and **English (EN)** with easy language switching.

## How It Works

### 1. **Translation Files**
- `src/lang/fr.php` - French translations
- `src/lang/en.php` - English translations

### 2. **Language Helper Functions**
Located in `src/helpers/language.php`:

#### **`t($key, $replace = [])`**
Get a translation by key using dot notation.

```php
// Simple usage
echo t('nav.dashboard'); // "Dashboard" or "Dashboard"

// With category
echo t('expenses.add_expense'); // "Add Expense" or "Ajouter une Dépense"

// With variable replacement
echo t('validation.min_length', ['min' => 8]); 
// "Must be at least 8 characters." or "Doit contenir au moins 8 caractères."
```

#### **`e($key, $replace = [])`**
Shorthand for `echo t()` - outputs translation directly.

```php
<h1><?php e('dashboard.title'); ?></h1>
<!-- Outputs: Dashboard or Dashboard -->
```

#### **`trans($key, $default, $replace = [])`**
Get translation with fallback default.

```php
echo trans('some.missing.key', 'Fallback Text');
// Returns 'Fallback Text' if key doesn't exist
```

#### **`getCurrentLanguage()`**
Get the current language code ('fr' or 'en').

```php
$lang = getCurrentLanguage(); // 'fr' or 'en'
```

#### **`setLanguage($lang)`**
Change the current language.

```php
setLanguage('en'); // Switch to English
setLanguage('fr'); // Switch to French
```

---

## Using Translations in Your Pages

### **Example 1: Simple Text**

```php
<h2><?php e('dashboard.welcome'); ?></h2>
<p><?php e('dashboard.overview'); ?></p>
```

### **Example 2: In Links/Buttons**

```php
<a href="accounts.php" class="btn btn-primary">
    <?php e('dashboard.manage_accounts'); ?>
</a>

<button class="btn btn-success">
    <?php e('expenses.add_expense'); ?>
</button>
```

### **Example 3: Form Labels**

```php
<div class="form-group">
    <label class="form-label"><?php e('expenses.expense_name'); ?></label>
    <input type="text" class="form-input" name="name" 
           placeholder="<?php echo t('expenses.expense_name'); ?>">
</div>

<div class="form-group">
    <label class="form-label"><?php e('common.amount'); ?></label>
    <input type="number" class="form-input" name="amount">
</div>
```

### **Example 4: Dropdown Options**

```php
<select class="form-select" name="frequency">
    <option value=""><?php e('expenses.select_frequency'); ?></option>
    <option value="one_time"><?php e('expenses.frequencies.one_time'); ?></option>
    <option value="daily"><?php e('expenses.frequencies.daily'); ?></option>
    <option value="weekly"><?php e('expenses.frequencies.weekly'); ?></option>
    <option value="monthly"><?php e('expenses.frequencies.monthly'); ?></option>
    <option value="yearly"><?php e('expenses.frequencies.yearly'); ?></option>
</select>
```

### **Example 5: Table Headers**

```php
<table class="table">
    <thead>
        <tr>
            <th><?php e('common.name'); ?></th>
            <th><?php e('common.amount'); ?></th>
            <th><?php e('common.date'); ?></th>
            <th><?php e('common.actions'); ?></th>
        </tr>
    </thead>
</table>
```

### **Example 6: Flash Messages**

```php
setFlashMessage('success', t('messages.success'));
setFlashMessage('error', t('messages.error'));
```

### **Example 7: Validation Messages**

```php
if (empty($name)) {
    $errors[] = t('validation.required');
}

if (strlen($password) < 8) {
    $errors[] = t('validation.min_length', ['min' => 8]);
}

if ($password !== $confirm_password) {
    $errors[] = t('validation.match');
}
```

---

## Language Switcher

The language switcher is automatically added to the header. Users can click the flag icon to toggle between languages.

### How to Change Language Programmatically

```php
// In any action file
if (isset($_POST['language'])) {
    setLanguage($_POST['language']);
    redirect('index.php');
}
```

---

## Adding New Translations

### Step 1: Add to English (`src/lang/en.php`)

```php
return [
    // ... existing translations
    
    'new_section' => [
        'title' => 'New Section',
        'description' => 'This is a new section',
        'action' => 'Do Something',
    ],
];
```

### Step 2: Add to French (`src/lang/fr.php`)

```php
return [
    // ... existing translations
    
    'new_section' => [
        'title' => 'Nouvelle Section',
        'description' => 'Ceci est une nouvelle section',
        'action' => 'Faire Quelque Chose',
    ],
];
```

### Step 3: Use in Your Code

```php
<h2><?php e('new_section.title'); ?></h2>
<p><?php e('new_section.description'); ?></p>
<button><?php e('new_section.action'); ?></button>
```

---

## Translation Keys Structure

All translations are organized by category:

- **`nav.*`** - Navigation menu items
- **`dashboard.*`** - Dashboard page
- **`accounts.*`** - Accounts page
- **`expenses.*`** - Expenses page
- **`incomes.*`** - Incomes page
- **`forecasts.*`** - Forecasts page
- **`subscriptions.*`** - Subscriptions page
- **`profile.*`** - Profile page
- **`auth.*`** - Authentication (login, signup, etc.)
- **`common.*`** - Common UI elements (buttons, labels, etc.)
- **`messages.*`** - Success/error messages
- **`validation.*`** - Form validation messages

---

## Best Practices

1. **Always use translation keys** instead of hardcoded text
2. **Use descriptive keys** like `expenses.add_expense` instead of generic keys
3. **Group related translations** under the same category
4. **Add both FR and EN** translations for every new key
5. **Use placeholders** for dynamic content: `t('validation.min_length', ['min' => 8])`

---

## Converting Existing Pages

To convert an existing page to use translations:

### Before:
```php
<h2>Dépenses</h2>
<button>Ajouter une Dépense</button>
```

### After:
```php
<h2><?php e('expenses.title'); ?></h2>
<button><?php e('expenses.add_expense'); ?></button>
```

---

## Language Switcher CSS

The language switcher has been styled to match the Earthen Luxe theme:
- Tan/cream background
- Smooth hover effects
- Flag emojis for visual cue
- Responsive design

---

## Testing

To test the bilingual system:
1. Log in to the application
2. Click the language toggle (🇬🇧 EN or 🇫🇷 FR) in the header
3. The entire interface should switch languages immediately

---

## Summary

✅ French and English translations ready  
✅ Easy-to-use helper functions (`t()`, `e()`)  
✅ Language switcher in header  
✅ Session-based language persistence  
✅ Translation keys organized by category  
✅ Support for variable replacement in translations  

**Next steps:** Convert your existing pages to use the translation system!
