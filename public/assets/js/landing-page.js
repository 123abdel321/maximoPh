// Navigation functionality
document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.getElementById("navbar")
  const navToggle = document.getElementById("navToggle")
  const mobileMenu = document.getElementById("mobileMenu")
  const navLinks = document.querySelectorAll(".nav-link, .mobile-link")

  // Theme toggle functionality
  const themeToggle = document.getElementById("themeToggle")
  const mobileThemeToggle = document.getElementById("mobileThemeToggle")

  // Check for saved theme preference or default to 'light'
  const currentTheme = localStorage.getItem("theme") || "light"
  document.documentElement.setAttribute("data-theme", currentTheme)

  function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute("data-theme")
    const newTheme = currentTheme === "dark" ? "light" : "dark"

    document.documentElement.setAttribute("data-theme", newTheme)
    localStorage.setItem("theme", newTheme)
  }

  // Add event listeners for both theme toggle buttons
  if (themeToggle) {
    themeToggle.addEventListener("click", toggleTheme)
  }

  if (mobileThemeToggle) {
    mobileThemeToggle.addEventListener("click", toggleTheme)
  }

  // Handle scroll effect on navbar
  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled")
    } else {
      navbar.classList.remove("scrolled")
    }
  })

  // Handle mobile menu toggle
  navToggle.addEventListener("click", () => {
    mobileMenu.classList.toggle("active")

    // Animate hamburger menu
    const hamburgers = navToggle.querySelectorAll(".hamburger")
    hamburgers.forEach((hamburger, index) => {
      if (mobileMenu.classList.contains("active")) {
        if (index === 0) {
          hamburger.style.transform = "rotate(45deg) translate(5px, 5px)"
        } else if (index === 1) {
          hamburger.style.opacity = "0"
        } else {
          hamburger.style.transform = "rotate(-45deg) translate(7px, -6px)"
        }
      } else {
        hamburger.style.transform = "none"
        hamburger.style.opacity = "1"
      }
    })
  })

  // Close mobile menu when clicking on links
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      mobileMenu.classList.remove("active")

      // Reset hamburger menu
      const hamburgers = navToggle.querySelectorAll(".hamburger")
      hamburgers.forEach((hamburger) => {
        hamburger.style.transform = "none"
        hamburger.style.opacity = "1"
      })
    })
  })

  // Smooth scrolling for anchor links
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const href = this.getAttribute("href")
      if (href.startsWith("#")) {
        e.preventDefault()
        const target = document.querySelector(href)
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          })
        }
      }
    })
  })

  // Add intersection observer for animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Observe elements for animation
  const animatedElements = document.querySelectorAll(".feature-card, .tax-card, .step, .benefit-item")
  animatedElements.forEach((el) => {
    el.style.opacity = "0"
    el.style.transform = "translateY(20px)"
    el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(el)
  })

  // Add hover effects to property cards
  const propertyCards = document.querySelectorAll(".property-card")
  propertyCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.05)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)"
    })
  })

  // Add click handlers for buttons
  const buttons = document.querySelectorAll(".btn")
  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      // Add ripple effect
      const ripple = document.createElement("span")
      const rect = this.getBoundingClientRect()
      const size = Math.max(rect.width, rect.height)
      const x = e.clientX - rect.left - size / 2
      const y = e.clientY - rect.top - size / 2

      ripple.style.width = ripple.style.height = size + "px"
      ripple.style.left = x + "px"
      ripple.style.top = y + "px"
      ripple.classList.add("ripple")

      this.appendChild(ripple)

      setTimeout(() => {
        ripple.remove()
      }, 600)
    })
  })
})

// Add CSS for ripple effect
const style = document.createElement("style")
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`
document.head.appendChild(style)