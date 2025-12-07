<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

class BillCalculator {
    private $items = [];
    private $subtotal = 0;
    private $taxAmount = 0;
    private $discountAmount = 0;
    private $total = 0;
    private $taxRate = 0;
    private $discountRate = 0;
    private $customerName = '';
    private $date = '';

    public function __construct($postData) {
        $this->customerName = htmlspecialchars($postData['customer_name'] ?? 'Customer');
        $this->date = $postData['date'] ?? date('Y-m-d');
        $this->taxRate = floatval($postData['tax_rate'] ?? 0);
        $this->discountRate = floatval($postData['discount'] ?? 0);
        
        // Process items
        if (isset($postData['items'])) {
            foreach ($postData['items'] as $item) {
                if (!empty($item['name']) && isset($item['quantity']) && isset($item['price'])) {
                    $quantity = intval($item['quantity']);
                    $price = floatval($item['price']);
                    $total = $quantity * $price;
                    
                    $this->items[] = [
                        'name' => htmlspecialchars($item['name']),
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $total
                    ];
                    
                    $this->subtotal += $total;
                }
            }
        }
        
        // Calculate tax
        $this->taxAmount = ($this->subtotal * $this->taxRate) / 100;
        
        // Calculate discount
        $this->discountAmount = ($this->subtotal * $this->discountRate) / 100;
        
        // Calculate total
        $this->total = $this->subtotal + $this->taxAmount - $this->discountAmount;
    }

    public function generateBillHTML() {
        $html = '<div class="bill-details">';
        $html .= '<div style="text-align: center; margin-bottom: 20px;">';
        $html .= '<h2 style="color: #4facfe; margin-bottom: 10px;">INVOICE</h2>';
        $html .= '<p><strong>Customer:</strong> ' . $this->customerName . '</p>';
        $html .= '<p><strong>Date:</strong> ' . $this->date . '</p>';
        $html .= '</div>';
        
        // Items table
        $html .= '<div style="overflow-x: auto;">';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<thead>';
        $html .= '<tr style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); color: white;">';
        $html .= '<th style="padding: 12px; text-align: left;">Item</th>';
        $html .= '<th style="padding: 12px; text-align: center;">Qty</th>';
        $html .= '<th style="padding: 12px; text-align: right;">Price (₹)</th>';
        $html .= '<th style="padding: 12px; text-align: right;">Total (₹)</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($this->items as $item) {
            $html .= '<tr style="border-bottom: 1px solid #eee;">';
            $html .= '<td style="padding: 12px;">' . $item['name'] . '</td>';
            $html .= '<td style="padding: 12px; text-align: center;">' . $item['quantity'] . '</td>';
            $html .= '<td style="padding: 12px; text-align: right;">' . number_format($item['price'], 2) . '</td>';
            $html .= '<td style="padding: 12px; text-align: right;">' . number_format($item['total'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Summary
        $html .= '<div style="background: #f8f9ff; padding: 20px; border-radius: 10px;">';
        $html .= '<div class="bill-row">';
        $html .= '<span>Subtotal:</span>';
        $html .= '<span>₹' . number_format($this->subtotal, 2) . '</span>';
        $html .= '</div>';
        
        if ($this->taxRate > 0) {
            $html .= '<div class="bill-row">';
            $html .= '<span>GST (' . $this->taxRate . '%):</span>';
            $html .= '<span>₹' . number_format($this->taxAmount, 2) . '</span>';
            $html .= '</div>';
        }
        
        if ($this->discountRate > 0) {
            $html .= '<div class="bill-row">';
            $html .= '<span>Discount (' . $this->discountRate . '%):</span>';
            $html .= '<span>-₹' . number_format($this->discountAmount, 2) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '<div class="bill-row" style="font-weight: bold; color: #667eea;">';
        $html .= '<span>TOTAL AMOUNT:</span>';
        $html .= '<span>₹' . number_format($this->total, 2) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getCustomerName() {
        return $this->customerName;
    }
}

// Main processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calculator = new BillCalculator($_POST);
    
    $billHTML = $calculator->generateBillHTML();
    $totalAmount = $calculator->getTotal();
    $customerName = $calculator->getCustomerName();
} else {
    // Redirect to form if accessed directly
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💰 Bill Calculation Result</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .bill-container {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .header {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .bill-content {
            padding: 40px;
        }

        .total-display {
            background: linear-gradient(90deg, #fa709a 0%, #fee140 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .total-display h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .amount {
            font-size: 4rem;
            font-weight: 800;
        }

        .button-group {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn {
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 117, 252, 0.3);
        }

        .print-btn {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 233, 123, 0.3);
        }

        .bill-details {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .bill-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #ddd;
        }

        .bill-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #667eea;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="header">
            <h1>
                <i class="fas fa-file-invoice-dollar"></i>
                Bill Calculation Result
            </h1>
            <p>Your detailed bill calculation</p>
        </div>

        <div class="bill-content">
            <!-- Success Message -->
            <div class="success-message" style="margin-bottom: 30px;">
                <i class="fas fa-check-circle"></i>
                <h3>Bill Calculated Successfully!</h3>
                <p>Thank you <?php echo htmlspecialchars($customerName); ?>! Here's your bill summary.</p>
            </div>

            <!-- Display Generated Bill -->
            <?php echo $billHTML; ?>

            <!-- Total Amount Display -->
            <div class="total-display">
                <h2>FINAL TOTAL AMOUNT</h2>
                <div class="amount">₹<?php echo number_format($totalAmount, 2); ?></div>
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <a href="index.html" class="btn back-btn">
                    <i class="fas fa-arrow-left"></i>
                    BACK TO CALCULATOR
                </a>
                
                <button onclick="window.print()" class="btn print-btn">
                    <i class="fas fa-print"></i>
                    PRINT BILL
                </button>
            </div>
        </div>
    </div>

    <script>
        // Add animation to total amount
        const amountElement = document.querySelector('.amount');
        let currentAmount = 0;
        const targetAmount = <?php echo $totalAmount; ?>;
        const duration = 1500;
        const steps = 60;
        const increment = targetAmount / steps;
        
        const animateAmount = () => {
            const timer = setInterval(() => {
                currentAmount += increment;
                if (currentAmount >= targetAmount) {
                    currentAmount = targetAmount;
                    clearInterval(timer);
                }
                amountElement.textContent = '₹' + currentAmount.toFixed(2);
            }, duration / steps);
        };
        
        // Start animation when page loads
        window.addEventListener('load', animateAmount);
    </script>
</body>
</html>