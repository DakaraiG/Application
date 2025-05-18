<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Goals</h2>
<button id="editGoalsButton">Edit Goals</button>
<div id="goalsModal" class="modal">
  <div class="modalContent">
    <span class="close">&times;</span>
    <h3>Set Your Goals</h3>
      <label for="start_date">Start Date:</label>
      <input type="date" id="start_date" name="start_date" required><br>

      <label for="end_date">End Date:</label>
      <input type="date" id="end_date" name="end_date" required><br>

      <label for="goals">Goals:</label>
      <textarea id="goals" name="goals" rows="6" required></textarea><br>

      <label for="savings_per_month">Savings Per Month:</label>
      <input type="number" id="savings_per_month" name="savings_per_month" required><br>

      <label for="current_savings">Current Savings:</label>
      <input type="number" id="current_savings" name="current_savings" required><br>

      <button type="submit">Save Goals</button>

      <button type="button" id="cancelButton">Cancel</button>
  </div>
</div>
<p><a href="logout.php">Logout</a></p>

<script>
  var modal = document.getElementById("goalsModal");

  var button = document.getElementById("editGoalsButton");

  var span = document.getElementsByClassName("close")[0];

  var cancelButton = document.getElementById("cancelButton");

  btn.onclick = function() {
    modal.style.display = "block";
  }

  span.onclick = function() {
    modal.style.display = "none";
  }
  cancelBtn.onclick = function() {
    modal.style.display = "none";
  }

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>