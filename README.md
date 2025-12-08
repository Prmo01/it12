# Construction Fabrication ERP System

A comprehensive ERP system built with Laravel 12+ for managing construction fabrication projects, procurement, inventory, and fabrication workflows.

## Features

- **Project Management**: Create and manage construction projects with timelines and budgets
- **Change Orders**: Handle project scope changes and timeline adjustments
- **Procurement Module**: 
  - Material Requisition (PR)
  - Request for Quotation (RFQ)
  - Supplier quotations comparison
  - Purchase Order generation and approval
  - Printable PO documents
- **Warehouse/Inventory Module**:
  - Stock-In (via Goods Receipt)
  - Stock-Out (via Material Issuance)
  - Stock Adjustments
  - Inventory dashboard with stock cards
- **Goods Receipt & Return**: Handle incoming goods and supplier returns
- **Fabrication Module**: Track material issuance, consumption, and finished goods
- **Reports Module**: Generate PDF, CSV, and JSON reports for:
  - Project consumption
  - Inventory movements
  - Supplier performance
  - Purchase history
  - Delayed projects

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js and NPM (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd it12project
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install frontend dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   Edit `.env` file and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=erp_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed database**
   ```bash
   php artisan db:seed
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

   Access the application at `http://localhost:8000`

## Default Login Credentials

After seeding:
- **Email**: admin@erp.com
- **Password**: password

## User Roles

- **Admin**: Full system access
- **Project Manager**: Manages projects and change orders
- **Purchasing**: Handles procurement (PR, RFQ, PO)
- **Warehouse**: Manages inventory and goods receipts
- **Fabrication**: Handles material issuance and fabrication jobs

## Workflow

1. **Client signs contract** → System creates Project
2. **Generate Purchase Request (PR)** → Request materials needed
3. **RFQ/Quotation** → Suppliers submit quotations
4. **Purchase Order (PO)** → Create PO from selected quotation
5. **Goods Receipt (GR)** → Receive goods from supplier
6. **Goods Return** (if needed) → Return damaged/excess items
7. **Inventory Stock-In** → Auto-updated via GR approval
8. **Material Issuance** → Issue materials to fabrication/project
9. **Fabrication** → Track fabrication jobs and consumption
10. **Change Orders** → Adjust project timeline and materials

## Database Structure

The system includes the following main tables:
- `users` - System users
- `roles` - User roles
- `projects` - Construction projects
- `change_orders` - Project change orders
- `inventory_items` - Inventory items
- `suppliers` - Supplier information
- `purchase_requests` - Material requisitions
- `quotations` - Supplier quotations
- `purchase_orders` - Purchase orders
- `goods_receipts` - Goods receipt records
- `goods_returns` - Goods return records
- `material_issuances` - Material issuance records
- `fabrication_jobs` - Fabrication job tracking
- `stock_movements` - Inventory movement history
- `audit_logs` - System audit trail

## Key Features

### Stock Management
All stock movements are automatically tracked in the `stock_movements` table. Stock levels are calculated in real-time based on:
- Goods Receipts (Stock-In)
- Material Issuances (Stock-Out)
- Stock Adjustments
- Goods Returns

### Reporting
Reports can be exported in multiple formats:
- **PDF**: Using barryvdh/laravel-dompdf
- **CSV**: For spreadsheet analysis
- **JSON**: For API integration

### Purchase Order Printing
Purchase orders can be printed as PDF documents with professional formatting.

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

## Scheduler

To enable automated tasks (if any), add to your crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Technologies Used

- **Backend**: Laravel 12+
- **Frontend**: Blade Templates, Bootstrap 5
- **Database**: MySQL/PostgreSQL/SQLite
- **PDF Generation**: barryvdh/laravel-dompdf
- **Charts**: Chart.js

## License

This project is proprietary software.

## Support

For issues and questions, please contact the development team.
