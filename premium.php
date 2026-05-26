<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

require 'config.php';

// Handle POST request for booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_booking') {
    header('Content-Type: application/json');

    try {
        // Verify nutritionist exists first
        $nutritionist_id = (int) $_POST['nutritionist_id'];
        $stmt = $conn->prepare("SELECT nutritionist_id FROM nutritionists WHERE name = ?");
        $stmt->execute([$_POST['nutritionist_name']]);
        $nutritionist = $stmt->fetch();
        
        if (!$nutritionist) {
            throw new Exception("Invalid nutritionist selected");
        }
        
        $nutritionist_id = $nutritionist['nutritionist_id'];

        // Start transaction
        $conn->beginTransaction();

        // Handle file upload
        $healthDocuments = null;
        if (isset($_FILES['health_documents'])) {
            $uploadDir = 'uploads/health_docs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['health_documents']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['health_documents']['tmp_name'], $targetPath)) {
                $healthDocuments = $targetPath;
            }
        }

        // Combine date and time for scheduled_time
        $scheduled_time = $_POST['scheduled_date'] . ' ' . $_POST['scheduled_time'] . ':00';

        // Insert into consultations table
        $stmt = $conn->prepare("
            INSERT INTO consultations (
                user_id, 
                nutritionist_id, 
                scheduled_time, 
                duration_minutes, 
                status, 
                payment_status, 
                payment_method, 
                amount, 
                notes, 
                health_documents,
                meeting_link
            ) VALUES (
                :user_id, 
                :nutritionist_id, 
                :scheduled_time, 
                :duration_minutes, 
                'Confirmed', 
                'Paid', 
                :payment_method, 
                :amount, 
                :notes, 
                :health_documents,
                (SELECT meeting_link FROM nutritionists WHERE nutritionist_id = :nutritionist_id2)
            )
        ");

        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':nutritionist_id' => $nutritionist_id,
            ':nutritionist_id2' => $nutritionist_id, // Need to pass this again for the subquery
            ':scheduled_time' => $scheduled_time,
            ':duration_minutes' => $_POST['duration_minutes'],
            ':payment_method' => $_POST['payment_method'],
            ':amount' => $_POST['amount'],
            ':notes' => $_POST['notes'],
            ':health_documents' => $healthDocuments
        ]);

        $consultation_id = $conn->lastInsertId();

        // Insert into payments table
        $paymentDetails = json_decode($_POST['payment_details'], true);
        $transaction_id = uniqid('TRX-');

        $stmt = $conn->prepare("
            INSERT INTO payments (
                consultation_id, 
                amount, 
                payment_method, 
                transaction_id, 
                status, 
                payment_details
            ) VALUES (
                :consultation_id, 
                :amount, 
                :payment_method, 
                :transaction_id, 
                'Completed', 
                :payment_details
            )
        ");

        $stmt->execute([
            ':consultation_id' => $consultation_id,
            ':amount' => $_POST['amount'],
            ':payment_method' => $_POST['payment_method'],
            ':transaction_id' => $transaction_id,
            ':payment_details' => $_POST['payment_details']
        ]);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'consultation_id' => $consultation_id,
            'meeting_link' => $conn->query("SELECT meeting_link FROM nutritionists WHERE nutritionist_id = " . $nutritionist_id)->fetchColumn()
        ]);
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php require 'Partials/nav.php'; ?>
  <?php require 'Partials/head.php'; ?>
  <style>
    :root {
      --green-light: #E3F4E1;
      --green: #5FB65A;
      --green-dark: #3C8D37;
      --gray-100: #F9FAFB;
      --gray-200: #F1F0FB;
      --gray-300: #E5E7EB;
      --gray-400: #D1D5DB;
      --gray-500: #9CA3AF;
      --gray-600: #6B7280;
      --gray-700: #4B5563;
      --gray-800: #1F2937;
      --gray-900: #111827;
      --border-radius: 0.75rem;
      --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --font-display: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Premium Page Styles */
    /* Header */
    .premium-header {
      background: linear-gradient(135deg, #3C8D37 0%, #5FB65A 100%);
      color: white;
      padding: 1.5rem 1rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      height: 180px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(63, 141, 55, 0.2);
    }


    .premium-header h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 2;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .premium-header p {
      font-size: 1rem;
      opacity: 0.9;
      position: relative;
      z-index: 2;
      max-width: 600px;
      margin: 0 auto;
    }


    /* Section Title Adjustments */
    .section-header {
      margin-top: 2.5rem;
      /* Increased spacing below banner */
      margin-bottom: 1.5rem;
    }

    .section-title {
      color: #3a7a36;
      /* Darker green for better contrast */
      font-size: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .section-title:after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 50px;
      height: 3px;
      background: #5FB65A;
      border-radius: 3px;
    }

    .nutritionist-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      padding: 2rem 1rem;
    }

    .nutritionist-card {
      background: white;
      border-radius: 0.75rem;
      box-shadow: var(--shadow-md);
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .nutritionist-card:hover {
      transform: translateY(-5px);
    }

    .nutritionist-photo {
      height: 200px;
      background-size: cover;
      background-position: center;

    }

    .nutri-1 {
      width: 300px;
      height: 300px;
    }

    .nutri-2 {
      width: 500px;
      height: 300px;
    }

    .nutri-3 {
      width: 500px;
      height: 300px;
    }

    .nutritionist-info {
      padding: 1.5rem;
    }

    .nutritionist-specialty {
      display: inline-block;
      background: var(--green-light);
      color: var(--green-dark);
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      margin-bottom: 0.5rem;
    }

    .nutritionist-rating {
      color: #FFC107;
      margin: 0.5rem 0;
    }

    .availability-badge {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.875rem;
    }

    .availability-online {
      color: var(--green);
    }

    .availability-offline {
      color: var(--gray-600);
    }

    .booking-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: white;
      border-radius: 0.75rem;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
    }

    .time-slot-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;
      margin: 1rem 0;
    }

    .time-slot {
      padding: 0.5rem;
      border: 1px solid var(--green-light);
      border-radius: 0.25rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .time-slot:hover {
      background: var(--green-light);
    }

    .time-slot.selected {
      background: var(--green);
      color: white;
      border-color: var(--green);
    }

    .time-slot.unavailable {
      opacity: 0.5;
      cursor: not-allowed;
      text-decoration: line-through;
    }

    .session-history {
      background: var(--gray-200);
      padding: 2rem 1rem;
    }

    .session-card {
      background: white;
      border-radius: 0.75rem;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: var(--shadow-md);
    }

    .upload-health {
      margin-top: 1rem;
      border-top: 1px dashed var(--gray-600);
      padding-top: 1rem;
    }

    /* Date Picker Styles */
    input[type="date"]:disabled {
      background-color: #f0f0f0;
      color: #999;
    }

    .payment-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    #paymentSummary p {
      margin-bottom: 0.5rem;
    }

    #paymentSummary p:last-child {
      margin-bottom: 0;
      font-weight: 600;
      color: var(--green-dark);
    }

    /* Confirmation Modal */
    .confirmation-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    .confirmation-content {
      background: white;
      border-radius: 0.75rem;
      width: 90%;
      max-width: 500px;
      padding: 2rem;
      text-align: center;
    }

    .confirmation-icon {
      font-size: 3rem;
      color: var(--green);
      margin-bottom: 1rem;
    }

    .meeting-link {
      display: inline-block;
      margin-top: 1rem;
      color: var(--green);
      text-decoration: underline;
    }

    /* Upload feedback */
    .upload-feedback {
      font-size: 0.875rem;
      color: var(--green-dark);
      margin-top: 0.5rem;
      display: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .time-slot-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }


    .has-error {
      border-color: #f87171 !important;
    }

    .error-message {
      color: #ef4444;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    /* Make the date picker show validation state */
    input:invalid {
      border-color: #f87171;
    }
  </style>
</head>

<body>

  <!-- Premium Consultation Content -->
  <main>
    <section class="premium-header">
      <h1>Premium Nutrition Consultations</h1>
      <p>Connect 1-on-1 with certified nutritionists tailored to your health goals</p>
    </section>

    <!-- Nutritionist Profiles -->
    <section class="container">
      <div class="section-header">
        <h2 class="section-title">Available Nutritionists</h2>
        <h3> Select a specialist and book your session</h3>
      </div>

      <div class="nutritionist-grid">
        <!-- Nutritionist 1 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc3.jpeg" class="nutri-1">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Diabetes & Weight Management</span>
            <h3>Dr. Aliya Hassan</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
              <span>(142 reviews)</span>
            </div>
            <p>10+ years experience helping patients manage blood sugar through nutrition.</p>
            <div class="availability-badge availability-online">
              <i class="fas fa-circle"></i>
              <span>Available today</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr. Aliya Hassan" data-nutritionist-id="1"
              data-specialty="Diabetes & Weight Management" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>

        <!-- Nutritionist 2 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc6.jpeg" class="nutri-2">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Pediatric Nutrition</span>
            <h3>Dr. Abdullah Khan</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <span>(89 reviews)</span>
            </div>
            <p>Specializes in child nutrition from infancy to adolescence.</p>
            <div class="availability-badge availability-online">
              <i class="fas fa-circle"></i>
              <span>Available tomorrow</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr. Abdullah Khan" data-nutritionist-id="2"
              data-specialty="Pediatric Nutrition" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>

        <!-- Nutritionist 3 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc2.jpeg" class="nutri-3">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Sports Nutrition</span>
            <h3>Dr.Hadiyah Malik</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="far fa-star"></i>
              <span>(67 reviews)</span>
            </div>
            <p>Helps athletes optimize performance through tailored nutrition plans.</p>
            <div class="availability-badge availability-offline">
              <i class="fas fa-circle"></i>
              <span>Available next week</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr.Hadiyah Malik" data-nutritionist-id="3"
              data-specialty="Sports Nutrition" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>

        <!-- Nutritionist 4 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc4.jpeg" class="nutri-1">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Gut Health & Digestion</span>
            <h3>Dr. Ali Rehman</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <span>(112 reviews)</span>
            </div>
            <p>Specializes in digestive disorders and gut microbiome optimization.</p>
            <div class="availability-badge availability-online">
              <i class="fas fa-circle"></i>
              <span>Available today</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr. Ali Rehman" data-nutritionist-id="4"
              data-specialty="Gut Health & Digestion" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>

        <!-- Nutritionist 5 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc7.jpeg" class="nutri-1">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Plant-Based Nutrition</span>
            <h3>Dr. Eman Javed</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="far fa-star"></i>
              <span>(76 reviews)</span>
            </div>
            <p>Expert in vegetarian and vegan nutrition planning.</p>
            <div class="availability-badge availability-offline">
              <i class="fas fa-circle"></i>
              <span>Available in 2 days</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr. Eman Javed" data-nutritionist-id="5"
              data-specialty="Plant-Based Nutrition" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>

        <!-- Nutritionist 6 -->
        <div class="nutritionist-card">
          <div class="nutritionist-photo">
            <img src="Images/doc5.jpeg" class="nutri-1">
          </div>
          <div class="nutritionist-info">
            <span class="nutritionist-specialty">Geriatric Nutrition</span>
            <h3>Dr. Harris Ali</h3>
            <div class="nutritionist-rating">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
              <span>(93 reviews)</span>
            </div>
            <p>Specializes in nutritional needs for older adults and seniors.</p>
            <div class="availability-badge availability-online">
              <i class="fas fa-circle"></i>
              <span>Available tomorrow</span>
            </div>
            <button class="btn btn-primary book-btn" data-nutritionist="Dr. Harris Ali" data-nutritionist-id="6"
              data-specialty="Geriatric Nutrition" style="margin-top: 1rem;">
              Book Session
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Booking Modal -->
    <div class="booking-modal" id="bookingModal">
      <div class="modal-content">
        <div class="modal-header"
          style="padding: 1rem; border-bottom: 1px solid var(--green-light); display: flex; justify-content: space-between; align-items: center;">
          <h3 id="modalNutritionistName">Book Session</h3>
          <button id="closeModal"
            style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1rem 1.5rem;">
          <p id="modalSpecialty" style="color: var(--gray-600); margin-bottom: 1rem;"></p>

          <h4 style="margin-bottom: 0.5rem;">Select Date</h4>
          <input type="date" id="sessionDate" class="form-control"
            style="width: 100%; padding: 0.5rem; border: 1px solid var(--green-light); border-radius: 0.25rem; margin-bottom: 1rem; ">

          <h4 style="margin-bottom: 0.5rem;">Available Time Slots</h4>
          <div class="time-slot-grid" id="timeSlots">

          </div>

          <div class="upload-health">
            <h4 style="margin-bottom: 0.5rem;">Additional Information</h4>
            <textarea id="healthNotes" rows="3"
              style="width: 100%; padding: 0.5rem; border: 1px solid var(--green-light); border-radius: 0.25rem; margin-bottom: 1rem;"
              placeholder="Any health concerns or questions for the nutritionist?"></textarea>
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.5rem;">
                <input type="file" id="healthDocuments" style="display: none;">
                <span class="btn btn-outline" style="display: inline-block; cursor: pointer;">
                  <i class="fas fa-upload" style="margin-right: 0.5rem;"></i>
                  Upload Health Reports
                </span>
              </label>
              <small style="color: var(--gray-600);">Max. 5MB (PDF, JPG, PNG)</small>
            </div>
          </div>

          <button id="confirmBooking" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
            Confirm Booking
          </button>
        </div>
      </div>
    </div>

    <!-- Session History -->
    <section class="session-history">
      <div class="container">
        <h2 class="section-title">Your Previous Sessions</h2>
        <p class="section-description">Review past consultations and feedback</p>

        <div class="session-card">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <h4>Dr.Aliya Hassan</h4>
            <span style="color: var(--gray-600); font-size: 0.875rem;">May 15, 2023</span>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.5rem;">Diabetes Management Plan</p>
          <div class="nutritionist-rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="far fa-star"></i>
          </div>
          <p style="margin-top: 0.5rem;">"Very knowledgeable about glycemic control strategies."</p>
        </div>

        <div class="session-card">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <h4>Dr. Abdullah Khan</h4>
            <span style="color: var(--gray-600); font-size: 0.875rem;">March 28, 2023</span>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.5rem;">Child Nutrition Consultation</p>
          <div class="nutritionist-rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p style="margin-top: 0.5rem;">"Excellent advice for picky eaters - my child loves the new meal ideas!"</p>
        </div>
      </div>
    </section>
  </main>

  <div class="payment-modal" id="paymentModal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
      <div class="modal-header"
        style="padding: 1rem; border-bottom: 1px solid var(--green-light); display: flex; justify-content: space-between; align-items: center;">
        <h3>Complete Payment</h3>
        <button id="closePaymentModal"
          style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
      </div>
      <div class="modal-body" style="padding: 1.5rem;">
        <div style="margin-bottom: 1.5rem;">
          <h4 style="margin-bottom: 0.5rem;">Consultation Summary</h4>
          <div id="paymentSummary"
            style="background: var(--gray-100); padding: 1rem; border-radius: var(--border-radius);"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select id="paymentMethod" class="form-control" style="margin-bottom: 1rem;">
            <option value="Credit Card">Credit/Debit Card</option>
            <option value="JazzCash">JazzCash</option>
            <option value="EasyPaisa">EasyPaisa</option>
            <option value="Bank Transfer">Bank Transfer</option>
          </select>
        </div>

        <div id="creditCardFields">
          <div class="form-group">
            <label class="form-label">Card Number</label>
            <input type="text" id="cardNumber" class="form-control" placeholder="1234 5678 9012 3456">
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Expiration Date</label>
              <input type="text" id="expiryDate" class="form-control" placeholder="MM/YY">
            </div>
            <div class="form-group">
              <label class="form-label">Security Code</label>
              <input type="text" id="cvc" class="form-control" placeholder="CVC">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Name on Card</label>
            <input type="text" id="cardName" class="form-control" placeholder="Full Name">
          </div>
        </div>

        <div id="mobilePaymentFields" style="display: none;">
          <div class="form-group">
            <label class="form-label">Mobile Account Number</label>
            <input type="text" id="mobileNumber" class="form-control" placeholder="03XX-XXXXXXX">
          </div>
        </div>

        <div id="bankTransferFields" style="display: none;">
          <div class="form-group">
            <label class="form-label">Bank Name</label>
            <input type="text" id="bankName" class="form-control" placeholder="e.g. HBL, UBL, etc.">
          </div>
          <div class="form-group">
            <label class="form-label">Transaction Reference</label>
            <input type="text" id="transactionRef" class="form-control" placeholder="Transaction ID">
          </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
          <label style="display: flex; align-items: flex-start; gap: 0.5rem;">
            <input type="checkbox" style="margin-top: 0.25rem;" required>
            <span>I agree to the <a href="#" style="color: var(--green);">Terms of Service</a> and authorize this
              payment</span>
          </label>
        </div>

        <button id="processPayment" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
          Pay PKR <span id="paymentAmount">0</span> and Confirm Booking
        </button>
      </div>
    </div>
  </div>

  <div class="confirmation-modal" id="confirmationModal">
    <div class="confirmation-content">
      <div class="confirmation-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h3>Appointment Confirmed!</h3>
      <div id="confirmationDetails" style="text-align: left; margin: 1.5rem 0;"></div>
      <p>You'll receive a confirmation email with all the details.</p>
      <a href="#" class="meeting-link" id="meetingLink" target="_blank">Join Meeting</a>
      <button class="btn btn-primary" style="margin-top: 1.5rem;" id="closeConfirmation">
        Close
      </button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // ======================
      // 1. INITIAL SETUP
      // ======================

      // Initialize Feather Icons
      feather.replace();

      // Set current year in footer
      document.getElementById('current-year').textContent = new Date().getFullYear();

      // ======================
      
      // ======================
      // 3. DROPDOWN MENUS
      // ======================
      const dropdowns = document.querySelectorAll('.dropdown');

      dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => {
          const dropdownContent = dropdown.querySelector('.dropdown-content');
          dropdownContent.style.display = 'block';
        });

        dropdown.addEventListener('mouseleave', () => {
          const dropdownContent = dropdown.querySelector('.dropdown-content');
          dropdownContent.style.display = 'none';
        });
      });

      // ======================
      // 4. BOOKING SYSTEM
      // ======================

      // DOM Elements
      const bookingModal = document.getElementById('bookingModal');
      const bookButtons = document.querySelectorAll('.book-btn');
      const closeModal = document.getElementById('closeModal');
      const modalNutritionistName = document.getElementById('modalNutritionistName');
      const modalSpecialty = document.getElementById('modalSpecialty');
      const timeSlotsContainer = document.getElementById('timeSlots');
      const sessionDate = document.getElementById('sessionDate');
      const healthDocuments = document.getElementById('healthDocuments');
      const confirmBookingBtn = document.getElementById('confirmBooking');

      // Confirmation Modal Elements
      const confirmationModal = document.getElementById('confirmationModal');
      const confirmationDetails = document.getElementById('confirmationDetails');
      const meetingLink = document.getElementById('meetingLink');
      const closeConfirmation = document.getElementById('closeConfirmation');

      // Payment Modal Elements
      const paymentModal = document.getElementById('paymentModal');
      const closePaymentModal = document.getElementById('closePaymentModal');
      const paymentSummary = document.getElementById('paymentSummary');
      const paymentMethodSelect = document.getElementById('paymentMethod');
      const creditCardFields = document.getElementById('creditCardFields');
      const mobilePaymentFields = document.getElementById('mobilePaymentFields');
      const bankTransferFields = document.getElementById('bankTransferFields');
      const paymentAmount = document.getElementById('paymentAmount');
      const processPaymentBtn = document.getElementById('processPayment');

      // Sample Data
      const availableSlots = {
        'Dr. Aliya Hassan': ['09:00 AM', '10:30 AM', '02:00 PM', '03:30 PM', '05:00 PM'],
        'Dr. Abdullah Khan': ['08:00 AM', '11:00 AM', '04:00 PM'],
        'Dr.Hadiyah Malik': ['10:00 AM', '01:30 PM', '03:00 PM', '06:00 PM'],
        'Dr. Ali Rehman': ['09:30 AM', '11:30 AM', '02:30 PM', '04:30 PM'],
        'Dr. Eman Javed': ['10:00 AM', '12:00 PM', '03:00 PM', '05:00 PM'],
        'Dr. Harris Ali': ['08:30 AM', '01:00 PM', '04:00 PM', '06:30 PM']
      };

      const nutritionistAvailability = {
        'Dr. Aliya Hassan': {
          unavailableDates: ['2023-07-15', '2023-07-20', '2023-07-22'],
          meetingLink: 'https://meet.nutricare.com/aliya-hassan'
        },
        'Dr. Abdullah Khan': {
          unavailableDates: ['2023-07-18', '2023-07-19'],
          meetingLink: 'https://meet.nutricare.com/abdullah-khan'
        },
        'Dr.Hadiyah Malik': {
          unavailableDates: ['2023-07-17', '2023-07-21'],
          meetingLink: 'https://meet.nutricare.com/hadiyah-malik'
        },
        'Dr. Ali Rehman': {
          unavailableDates: ['2023-07-16', '2023-07-23'],
          meetingLink: 'https://meet.nutricare.com/ali-rehman'
        },
        'Dr. Eman Javed': {
          unavailableDates: ['2023-07-14', '2023-07-24'],
          meetingLink: 'https://meet.nutricare.com/eman-javed'
        },
        'Dr. Harris Ali': {
          unavailableDates: ['2023-07-13', '2023-07-25'],
          meetingLink: 'https://meet.nutricare.com/harris-ali'
        }
      };

      let currentNutritionist = '';
      let currentNutritionistId = 0;

      // ======================
      // 4.1 FILE UPLOAD HANDLING
      // ======================

      // Create upload feedback element
      const uploadFeedback = document.createElement('div');
      uploadFeedback.className = 'upload-feedback';
      healthDocuments.parentNode.appendChild(uploadFeedback);

      healthDocuments.addEventListener('change', function () {
        if (this.files.length > 0) {
          const file = this.files[0];

          // Validate file size (5MB limit)
          if (file.size > 5 * 1024 * 1024) {
            uploadFeedback.textContent = 'File too large (max 5MB)';
            uploadFeedback.style.color = 'red';
            this.value = ''; // Clear the invalid file
          } else {
            uploadFeedback.textContent = `File ready: ${file.name}`;
            uploadFeedback.style.color = 'var(--green-dark)';
          }
          uploadFeedback.style.display = 'block';
        }
      });

      // ======================
      // 4.2 DATE PICKER LOGIC
      // ======================

      function setupDatePicker(nutritionist, nutritionistId) {
        currentNutritionist = nutritionist;
        currentNutritionistId = nutritionistId;
        const unavailableDates = nutritionistAvailability[nutritionist].unavailableDates;

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        sessionDate.setAttribute('min', today);

        // Reset date picker
        sessionDate.value = '';
        timeSlotsContainer.innerHTML = '';

        // In a real app, you would disable specific dates here
        console.log(`Unavailable dates for ${nutritionist}:`, unavailableDates);
      }

      // Handle date selection changes
      sessionDate.addEventListener('change', function () {
        if (!currentNutritionist) return;

        // Clear previous time slots
        timeSlotsContainer.innerHTML = '';

        // Get day of week (0-6, Sunday-Saturday)
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.getDay();

        // Generate time slots based on day of week
        let availableSlotsForDay = [];

        if (dayOfWeek === 0 || dayOfWeek === 6) {
          // Weekend - limited slots
          availableSlotsForDay = ['10:00 AM', '02:00 PM'];
        } else {
          // Weekday - normal slots
          availableSlotsForDay = availableSlots[currentNutritionist];
        }

        // Populate time slots
        availableSlotsForDay.forEach(slot => {
          const slotElement = document.createElement('div');
          slotElement.className = 'time-slot';
          slotElement.textContent = slot;
          slotElement.addEventListener('click', function () {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
          });
          timeSlotsContainer.appendChild(slotElement);
        });
      });

      // ======================
      // 4.3 BOOKING MODAL HANDLERS
      // ======================

      // Open booking modal when a nutritionist is selected
      bookButtons.forEach(button => {
        button.addEventListener('click', function () {
          const nutritionist = this.getAttribute('data-nutritionist');
          const nutritionistId = this.getAttribute('data-nutritionist-id');
          const specialty = this.getAttribute('data-specialty');

          modalNutritionistName.textContent = `Book with ${nutritionist}`;
          modalSpecialty.textContent = specialty;

          // Setup date picker with nutritionist's availability
          setupDatePicker(nutritionist, nutritionistId);

          bookingModal.style.display = 'flex';
        });
      });

      // Close booking modal
      closeModal.addEventListener('click', function () {
        bookingModal.style.display = 'none';
      });

      // Close when clicking outside modal
      window.addEventListener('click', function (event) {
        if (event.target === bookingModal) {
          bookingModal.style.display = 'none';
        }
        if (event.target === paymentModal) {
          paymentModal.style.display = 'none';
        }
      });

      // ======================
      // 4.4 PAYMENT MODAL HANDLERS
      // ======================

      // Show appropriate payment fields based on selection
      paymentMethodSelect.addEventListener('change', function () {
        const method = this.value;
        creditCardFields.style.display = 'none';
        mobilePaymentFields.style.display = 'none';
        bankTransferFields.style.display = 'none';

        if (method === 'Credit Card') {
          creditCardFields.style.display = 'block';
        } else if (method === 'JazzCash' || method === 'EasyPaisa') {
          mobilePaymentFields.style.display = 'block';
        } else if (method === 'Bank Transfer') {
          bankTransferFields.style.display = 'block';
        }
      });

      // Close payment modal
      closePaymentModal.addEventListener('click', function () {
        paymentModal.style.display = 'none';
      });

      // ======================
      // 4.5 FORM VALIDATION AND BOOKING CONFIRMATION
      // ======================

      // Validate booking form
      function validateBookingForm() {
        let isValid = true;

        // Reset error states
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));

        // Validate date
        if (!sessionDate.value) {
          showError(sessionDate, 'Please select a date');
          isValid = false;
        }

        // Validate time slot
        if (!document.querySelector('.time-slot.selected')) {
          const timeSlotLabel = document.createElement('div');
          timeSlotLabel.className = 'error-message';
          timeSlotLabel.textContent = 'Please select a time slot';
          timeSlotLabel.style.color = 'red';
          timeSlotLabel.style.marginTop = '-0.5rem';
          timeSlotLabel.style.marginBottom = '1rem';
          timeSlotsContainer.parentNode.insertBefore(timeSlotLabel, timeSlotsContainer.nextSibling);
          isValid = false;
        }

        return isValid;
      }

      // Validate payment form
      function validatePaymentForm() {
        let isValid = true;
        const method = paymentMethodSelect.value;

        // Reset error states
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));

        // Validate payment method specific fields
        if (method === 'Credit Card') {
          const cardNumber = creditCardFields.querySelector('input[type="text"]');
          const expiry = creditCardFields.querySelector('input[placeholder="MM/YY"]');
          const cvc = creditCardFields.querySelector('input[placeholder="CVC"]');
          const name = creditCardFields.querySelector('input[placeholder="Full Name"]');

          if (!cardNumber.value) showError(cardNumber, 'Card number is required');
          if (!expiry.value) showError(expiry, 'Expiry date is required');
          if (!cvc.value) showError(cvc, 'CVC is required');
          if (!name.value) showError(name, 'Name on card is required');

          if (!cardNumber.value || !expiry.value || !cvc.value || !name.value) {
            isValid = false;
          }
        }
        else if (method === 'JazzCash' || method === 'EasyPaisa') {
          const mobileNumber = mobilePaymentFields.querySelector('input');
          if (!mobileNumber.value) {
            showError(mobileNumber, 'Mobile account number is required');
            isValid = false;
          }
        }
        else if (method === 'Bank Transfer') {
          const bankName = bankTransferFields.querySelector('input[placeholder="e.g. HBL, UBL, etc."]');
          const transactionRef = bankTransferFields.querySelector('input[placeholder="Transaction ID"]');

          if (!bankName.value) showError(bankName, 'Bank name is required');
          if (!transactionRef.value) showError(transactionRef, 'Transaction reference is required');

          if (!bankName.value || !transactionRef.value) {
            isValid = false;
          }
        }

        // Validate terms checkbox
        const termsCheckbox = document.querySelector('#paymentModal input[type="checkbox"]');
        if (!termsCheckbox.checked) {
          const termsError = document.createElement('div');
          termsError.className = 'error-message';
          termsError.textContent = 'You must accept the terms to proceed';
          termsError.style.color = 'red';
          termsError.style.marginTop = '0.5rem';
          termsCheckbox.parentNode.parentNode.appendChild(termsError);
          isValid = false;
        }

        return isValid;
      }

      // Helper function to show error messages
      function showError(inputElement, message) {
        inputElement.classList.add('has-error');
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.textContent = message;
        errorMessage.style.color = 'red';
        errorMessage.style.fontSize = '0.875rem';
        errorMessage.style.marginTop = '0.25rem';
        inputElement.parentNode.appendChild(errorMessage);
      }

      // ======================
      // 4.6 CONFIRM BOOKING HANDLER
      // ======================
      confirmBookingBtn.addEventListener('click', function () {
        // Validate form before proceeding
        if (!validateBookingForm()) {
          return;
        }

        const selectedSlot = document.querySelector('.time-slot.selected');
        const nutritionist = modalNutritionistName.textContent.replace('Book with ', '');
        const date = sessionDate.value;
        const notes = document.getElementById('healthNotes').value;
        const files = healthDocuments.files;

        // Format date for display
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });

        // Set consultation fee based on nutritionist (in PKR)
        let fee = 3000; // Default fee
        if (nutritionist === "Dr. Aliya Hassan") fee = 3500;
        if (nutritionist === "Dr. Abdullah Khan") fee = 4000;
        if (nutritionist === "Dr.Hadiyah Malik") fee = 4500;

        // Update payment summary
        paymentSummary.innerHTML = `
          <p><strong>Nutritionist:</strong> ${nutritionist}</p>
          <p><strong>Date:</strong> ${formattedDate}</p>
          <p><strong>Time:</strong> ${selectedSlot.textContent}</p>
          <p><strong>Consultation Fee:</strong> PKR ${fee.toLocaleString()}</p>
        `;

        // Update payment amount
        paymentAmount.textContent = fee.toLocaleString();

        // Store booking details for confirmation
        window.currentBooking = {
          nutritionist,
          nutritionistId: currentNutritionistId,
          specialty: modalSpecialty.textContent,
          scheduled_date: date,
          scheduled_time: selectedSlot.textContent,
          scheduledDateTime: date + ' ' + convertTimeTo24Hour(selectedSlot.textContent),
          date: formattedDate,
          time: selectedSlot.textContent,
          notes,
          files: files.length > 0 ? files[0].name : 'None',
          fee
        };

        // Hide booking modal, show payment modal
        bookingModal.style.display = 'none';
        paymentModal.style.display = 'flex';
      });

      // ======================
      // 5. PROCESS PAYMENT HANDLER
      // ======================
      processPaymentBtn.addEventListener('click', async function () {
        // Validate payment form before proceeding
        if (!validatePaymentForm()) {
          return;
        }

        const booking = window.currentBooking;
        const paymentMethod = paymentMethodSelect.value;

        // Get payment details based on method
        let paymentDetails = {};
        if (paymentMethod === 'Credit Card') {
          paymentDetails = {
            cardNumber: document.getElementById('cardNumber').value,
            expiryDate: document.getElementById('expiryDate').value,
            cvc: document.getElementById('cvc').value,
            cardName: document.getElementById('cardName').value
          };
        } else if (paymentMethod === 'JazzCash' || paymentMethod === 'EasyPaisa') {
          paymentDetails = {
            mobileNumber: document.getElementById('mobileNumber').value
          };
        } else if (paymentMethod === 'Bank Transfer') {
          paymentDetails = {
            bankName: document.getElementById('bankName').value,
            transactionRef: document.getElementById('transactionRef').value
          };
        }

        // Create FormData to send to server
        const formData = new FormData();
        formData.append('action', 'process_booking');
        formData.append('nutritionist_name', booking.nutritionist);
        formData.append('nutritionist_id', booking.nutritionistId);
        formData.append('scheduled_date', booking.scheduled_date);
        formData.append('scheduled_time', booking.scheduled_time);
        formData.append('duration_minutes', '60');
        formData.append('notes', booking.notes);
        formData.append('payment_method', paymentMethod);
        formData.append('amount', booking.fee.toString());
        formData.append('payment_details', JSON.stringify(paymentDetails));

        // If there's a file, add it to the form data
        const fileInput = document.getElementById('healthDocuments');
        if (fileInput.files.length > 0) {
          formData.append('health_documents', fileInput.files[0]);
        }

        try {
          // Show loading state
          processPaymentBtn.disabled = true;
          processPaymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

          // Send booking data to server
          const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
          });

          const data = await response.json();

          if (data.success) {
            // Update confirmation details
            confirmationDetails.innerHTML = `
              <p><strong>Nutritionist:</strong> ${booking.nutritionist}</p>
              <p><strong>Specialization:</strong> ${booking.specialty}</p>
              <p><strong>Date:</strong> ${booking.date}</p>
              <p><strong>Time:</strong> ${booking.time}</p>
              <p><strong>Consultation Fee:</strong> PKR ${booking.fee.toLocaleString()}</p>
              <p><strong>Payment Method:</strong> ${paymentMethod}</p>
              <p><strong>Confirmation Number:</strong> ${data.consultation_id}</p>
              ${booking.notes ? `<p><strong>Your Notes:</strong> ${booking.notes}</p>` : ''}
              ${booking.files !== 'None' ? `<p><strong>Attachments:</strong> ${booking.files}</p>` : ''}
            `;

            // Set meeting link
            meetingLink.href = data.meeting_link;

            paymentModal.style.display = 'none';
            confirmationModal.style.display = 'flex';
          } else {
            alert('Error: ' + data.message);
          }
        } catch (error) {
          console.error('Error:', error);
          alert('An error occurred while processing your booking. Please try again.');
        } finally {
          // Reset button state
          processPaymentBtn.disabled = false;
          processPaymentBtn.innerHTML = `Pay PKR ${booking.fee.toLocaleString()} and Confirm Booking`;
        }
      });

      // Close confirmation modal
      closeConfirmation.addEventListener('click', function () {
        confirmationModal.style.display = 'none';
      });

      // Helper function to convert AM/PM time to 24-hour format
      function convertTimeTo24Hour(time) {
        const [timePart, modifier] = time.split(' ');
        let [hours, minutes] = timePart.split(':');

        if (hours === '12') {
          hours = '00';
        }

        if (modifier === 'PM') {
          hours = parseInt(hours, 10) + 12;
        }

        return hours + ':' + minutes + ':00';
      }
    });
  </script>

</body>
<?php require 'Partials/footer.php'; ?>

</html>
