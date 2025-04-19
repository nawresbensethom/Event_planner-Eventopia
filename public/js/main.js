// Main JavaScript file for Eventopia

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add animation to elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 50) {
                element.classList.add('fade-in');
            }
        });
    };

    // Run animation check on load and scroll
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });

    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordField = document.querySelector(this.getAttribute('data-target'));
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // Countdown timer for events
    const countdownElements = document.querySelectorAll('.countdown');
    
    countdownElements.forEach(element => {
        const targetDate = new Date(element.getAttribute('data-target-date')).getTime();
        
        const updateCountdown = function() {
            const now = new Date().getTime();
            const distance = targetDate - now;
            
            if (distance < 0) {
                element.innerHTML = 'Événement terminé';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            element.innerHTML = `${days}j ${hours}h ${minutes}m ${seconds}s`;
        };
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });

    // Filter functionality for services and events
    const filterButtons = document.querySelectorAll('.filter-button');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            const items = document.querySelectorAll('.filterable-item');
            
            // Update active button
            document.querySelector('.filter-button.active')?.classList.remove('active');
            this.classList.add('active');
            
            items.forEach(item => {
                if (filterValue === 'all' || item.classList.contains(filterValue)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Rating system
    const ratingInputs = document.querySelectorAll('.rating input');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const ratingValue = this.value;
            const ratingDisplay = this.closest('.rating-container').querySelector('.rating-value');
            
            if (ratingDisplay) {
                ratingDisplay.textContent = ratingValue;
            }
        });
    });

    // Drag and drop for todo lists
    const draggableElements = document.querySelectorAll('.draggable');
    
    draggableElements.forEach(element => {
        element.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.id);
            this.classList.add('dragging');
        });
        
        element.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });
    
    const dropZones = document.querySelectorAll('.drop-zone');
    
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        zone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const draggedElementId = e.dataTransfer.getData('text/plain');
            const draggedElement = document.getElementById(draggedElementId);
            
            if (draggedElement && this.classList.contains('drop-zone')) {
                this.appendChild(draggedElement);
                
                // Update task status via AJAX if needed
                const taskId = draggedElement.getAttribute('data-task-id');
                const newStatus = this.getAttribute('data-status');
                
                if (taskId && newStatus) {
                    // AJAX call would go here
                    console.log(`Task ${taskId} status updated to ${newStatus}`);
                }
            }
        });
    });
});
