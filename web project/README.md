# NO CAP - E-Commerce Website (PHP & MySQL)

A clean, standard PHP + MySQL e-commerce store with an AI Shopping Assistant.

---

## How to Run on Linux (Step-by-Step)

### Step 1: Place the Project
Copy the `nocap_linux` folder into your preferred location, for example:
- **Option A (Standalone):** Put it in your home folder: `~/nocap_linux`
- **Option B (Apache webroot):** Put it in `/var/www/html/nocap`

### Step 2: Start MySQL Service
Open terminal and start MySQL:
```bash
sudo systemctl start mysql
```

### Step 3: Import the Database
Import `database.sql` into MySQL:
```bash
mysql -u root -p < database.sql
```
*(If your MySQL root has no password, just press Enter when asked).*

### Step 4: Run the Website
Inside the `nocap_linux` folder, run:
```bash
chmod +x setup_linux.sh run_dev.sh
./run_dev.sh
```
*(Or simply run: `php -S localhost:8000`)*

### Step 5: Open in Browser
- Store: `http://localhost:8000`
- Admin Panel: `http://localhost:8000/admin/`

---

## Logins

- **Admin Account:**
  - Email: `admin@nocap.com`
  - Password: `admin123`
  - Panel: `/admin/index.php`
- **Customer Account:**
  - Register any new account on the `/register.php` page.

---

## Settings: `includes/config.php`
- **Database Connection:** `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- **Store Settings:** `CURRENCY` (Rs.), `SHIPPING_FEE` (200), `FREE_SHIPPING_ABOVE` (5000)
- **AI Assistant:** Works out of the box using built-in catalog search. Optionally add `ANTHROPIC_API_KEY`.

---

## How the AI Works (For Viva Questions)

**Examiner Question:** *"How does the AI Shopping Assistant work?"*
**Answer:**
1. When the user types a question in the chat widget, an AJAX POST request is sent to `assistant.php`.
2. `assistant.php` reads the question and analyzes keywords and budget (e.g. "under 5000" or category like "hoodies" or "sneakers").
3. It queries the MySQL `products` table using PDO prepared statements to find the best matching in-stock items.
4. It formats product links dynamically (`[[id]]` turns into clickable `<a href="product.php?id=...">`) and sends back a JSON response.
5. If an API key is provided in `config.php`, it can also send the catalog to the Claude API via cURL for generative responses.

---

## Project Structure

```
nocap_linux/
├── index.php             # Home page
├── products.php          # Product catalog, search & filtering
├── product.php           # Single product detail view
├── cart.php              # Shopping cart
├── checkout.php          # Order checkout (Cash on Delivery)
├── order_success.php     # Order confirmation page
├── orders.php            # Customer order history
├── login.php             # User login
├── register.php          # User registration
├── logout.php            # Sign out
├── assistant.php         # AI assistant endpoint
├── database.sql          # Database schema & initial products
├── admin/                # Admin dashboard, products management, orders
├── includes/             # config.php, db.php, functions.php, header.php, footer.php
├── assets/               # CSS & JavaScript
└── uploads/              # Product image uploads
```
