  AOS.init({ duration: 700, easing: "ease-out-cubic", once: true, offset: 80 });

  const scrollTopBtn = document.getElementById("scrollTop");
  window.addEventListener("scroll", () => {
    if (!scrollTopBtn) return;
    if (window.scrollY > 400) scrollTopBtn.classList.add("visible");
    else scrollTopBtn.classList.remove("visible");
  });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  const heroSection = document.getElementById("home");
  const counters = document.querySelectorAll(".counter-number");
  function animateCounters() {
    counters.forEach((counter) => {
      const target = Number(counter.getAttribute("data-target") || 0);
      const increment = target / (2000 / 16);
      let current = 0;
      const update = () => {
        current += increment;
        if (current < target) {
          counter.textContent = Math.floor(current).toLocaleString("pt-BR");
          requestAnimationFrame(update);
        } else {
          counter.textContent = target.toLocaleString("pt-BR");
        }
      };
      update();
    });
  }

  if (heroSection && counters.length > 0) {
    const heroObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounters();
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    heroObserver.observe(heroSection);
  }

  const contactForm = document.getElementById("contactForm");
  if (contactForm) {
    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      alert("Mensagem enviada com sucesso! Entraremos em contato em breve.");
      this.reset();
    });
  }
