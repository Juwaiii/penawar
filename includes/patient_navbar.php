<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Patient Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="book_appointment.php">Book Appointment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="medical_records.php">Medical Records</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="prescriptions.php">Prescriptions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bill.php">Bills</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>