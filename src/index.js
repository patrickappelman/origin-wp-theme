// import Person from "./scripts/Person"
// import ExampleReactComponent from "./scripts/ExampleReactComponent"
// import React from "react"
// import ReactDOM from "react-dom/client"

// const person1 = new Person("Brad")
// if (document.querySelector("#render-react-example-here")) {
//   const root = ReactDOM.createRoot(document.querySelector("#render-react-example-here"))
//   root.render(<ExampleReactComponent />)
// }

// REQUIRE PRELINE

require("preline/dist/preline.js");

// SET BREAKPOINTS

const break_sm = 640;
const break_md = 768;
const break_lg = 1024;
const break_xl = 1280;
const break_2xl = 1920;

// SHOW/HIDE HEADER ON SCROLL

document.addEventListener("DOMContentLoaded", () => {
	const header = document.querySelector(".header");
	const headerHeight = header.offsetHeight;

	// Minimum scroll distance in pixels to trigger class change
	const scrollThreshold = 100;
	let lastScrollY = window.scrollY;
	let lastClassChangeY = lastScrollY;

	window.addEventListener("scroll", () => {
		const currentScrollY = window.scrollY;

		if (currentScrollY > headerHeight) {
			const scrollDelta = Math.abs(currentScrollY - lastClassChangeY);

			if (scrollDelta >= scrollThreshold) {
				if (currentScrollY > lastScrollY) {
					// Scrolling down
					header.classList.add("header--hide");
					lastClassChangeY = currentScrollY;
				} else {
					// Scrolling up
					header.classList.remove("header--hide");
					lastClassChangeY = currentScrollY;
				}
			}
		} else {
			// Above header height, ensure header is visible
			header.classList.remove("header--hide");
			lastClassChangeY = currentScrollY;
		}

		lastScrollY = currentScrollY;
	});
});

// OPEN ALL EXTERNAL LINKS IN NEW WINDOW

document.addEventListener("DOMContentLoaded", () => {
	// Get the current domain
	const currentDomain = window.location.hostname;

	// Get all links on the page
	const links = document.querySelectorAll("a[href]");

	links.forEach((link) => {
		try {
			// Create a URL object to parse the link's href
			const url = new URL(link.href, window.location.origin);
			const linkDomain = url.hostname;

			// Check if the link's domain is different from the current domain
			if (linkDomain && linkDomain !== currentDomain) {
				// Add target="_blank" if not present
				if (!link.hasAttribute("target") || link.getAttribute("target") !== "_blank") {
					link.setAttribute("target", "_blank");
				}
				// Add rel="noopener noreferrer" for security if not present
				if (!link.hasAttribute("rel") || !link.getAttribute("rel").includes("noopener")) {
					link.setAttribute("rel", "noopener noreferrer");
				}
			}
		} catch (e) {
			// Handle invalid URLs (e.g., malformed href or relative URLs that can't be resolved)
			console.warn(`Invalid URL in link: ${link.href}`, e);
		}
	});
});

// STICKY PANEL

document.addEventListener("DOMContentLoaded", () => {
	const sidebarDetails = document.querySelector(".sticky__sidebar-details");
	const sidebar = document.querySelector(".sticky__sidebar");
	const contentContainer = document.querySelector(".sticky__content");

	if (!sidebarDetails || !sidebar || !contentContainer) return;

	const isAboveLargeBreakpoint = () => window.innerWidth >= break_lg;

	function handleSticky() {
		if (!isAboveLargeBreakpoint()) {
			sidebarDetails.style.position = "static";
			sidebarDetails.style.top = "";
			sidebarDetails.style.bottom = "";
			sidebarDetails.style.left = "";
			sidebarDetails.style.width = "";
			return;
		}

		const sidebarRect = sidebar.getBoundingClientRect();
		const contentRect = contentContainer.getBoundingClientRect();
		const detailsRect = sidebarDetails.getBoundingClientRect();

		// Get sidebar padding
		const sidebarStyles = window.getComputedStyle(sidebar);
		const paddingLeft = parseFloat(sidebarStyles.paddingLeft);
		const paddingRight = parseFloat(sidebarStyles.paddingRight);
		const paddingTop = parseFloat(sidebarStyles.paddingTop);

		// Calculate boundaries
		const contentBottom = contentRect.bottom + window.scrollY;
		const sidebarTop = sidebarRect.top + window.scrollY;
		const windowTop = window.scrollY;

		// Calculate width and left position to respect sidebar padding
		const sidebarInnerWidth = sidebarRect.width - paddingLeft - paddingRight;
		const sidebarLeft = sidebarRect.left + paddingLeft;

		if (windowTop >= sidebarTop) {
			// Start sticking when sidebar reaches top
			sidebarDetails.style.position = "fixed";
			sidebarDetails.style.top = `${paddingTop}px`; // Respect sidebar's top padding
			sidebarDetails.style.left = `${sidebarLeft}px`;
			sidebarDetails.style.width = `${sidebarInnerWidth}px`;
			sidebarDetails.style.bottom = ""; // Clear bottom when fixed

			// Stop at content container bottom
			if (windowTop + detailsRect.height + paddingTop > contentBottom) {
				sidebarDetails.style.position = "absolute";
				sidebarDetails.style.top = ""; // Remove top attribute
				sidebarDetails.style.bottom = "0px"; // Anchor to bottom of sidebar
				sidebarDetails.style.left = `${paddingLeft}px`;
				sidebarDetails.style.width = `${sidebarInnerWidth}px`;
			}
		} else {
			// Reset when scrolling back up
			sidebarDetails.style.position = "static";
			sidebarDetails.style.top = "";
			sidebarDetails.style.bottom = "";
			sidebarDetails.style.left = "";
			sidebarDetails.style.width = "";
		}
	}

	// Run on scroll and resize
	window.addEventListener("scroll", handleSticky);
	window.addEventListener("resize", handleSticky);

	// Initial check
	handleSticky();
});

// FADEUP ANIMATIONS

document.addEventListener("DOMContentLoaded", () => {
	const fadeUpObserver = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add("fade-up--faded");
					fadeUpObserver.unobserve(entry.target);
				}
			});
		},
		{
			threshold: 0.15,
		}
	);

	// Get viewport height
	const viewportHeight = window.innerHeight;

	// Select all elements with fade-up class
	document.querySelectorAll(".fade-up").forEach((el) => {
		// Get element's position relative to the viewport
		const rect = el.getBoundingClientRect();

		// If element is above or partially in the initial viewport, apply fade immediately
		if (rect.top < viewportHeight && rect.bottom > 0) {
			el.classList.add("fade-up--faded");
		} else {
			// Observe elements below the fold
			fadeUpObserver.observe(el);
		}
	});
});

// COUNTUP ANIMATIONS

import { CountUp } from "../node_modules/countup.js/dist/countup.min.js";

document.addEventListener("DOMContentLoaded", () => {
	const counterOps = {
		duration: 3,
	};
	const counterObserver = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					const num = +entry.target.getAttribute("data-target");

					let counter = new CountUp(entry.target, num, counterOps);

					if (!counter.error) {
						counter.start();
					} else {
						console.error(counter.error);
					}

					entry.target.classList.add("counter--counted");
					counterObserver.unobserve(entry.target);
				}
			});
		},
		{
			threshold: 1,
		}
	);

	document.querySelectorAll(".counter").forEach((el) => {
		counterObserver.observe(el);
	});
});

// MARQUEE ANIMATIONS

function initMarquee() {
	const marquee = document.querySelector(".marquee");
	const companyCarouselList = document.querySelector(".company-carousel__list");
	let speed = 1; // Pixels per frame
	let position = 0;

	if (companyCarouselList) {
		// Duplicate logos in JavaScript for seamless scrolling
		const logos = companyCarouselList.innerHTML;
		const duplicateContent = document.createElement("ul");
		duplicateContent.classList.add("company-carousel__list", "flex");
		duplicateContent.innerHTML = logos;
		duplicateContent.setAttribute("aria-hidden", "true");
		marquee.appendChild(duplicateContent);

		// Responsive width adjustments
		const updateMarqueeWidth = () => {
			const width = window.innerWidth;
			let visibleLogos = 6; // Desktop default
			if (width < 640) {
				visibleLogos = 2; // Mobile
			} else if (width < 1024) {
				visibleLogos = 4; // Tablet
			}
			marquee.style.width = `${visibleLogos * (150 + 32)}px`; // Logo width (150) + margin (32)
		};

		// Initial width setup
		updateMarqueeWidth();
		window.addEventListener("resize", updateMarqueeWidth);

		// Animation loop
		function scroll() {
			position -= speed;
			marquee.style.transform = `translateX(${position}px)`;

			// Reset position when first set of logos is fully out of view
			if (Math.abs(position) >= companyCarouselList.offsetWidth) {
				position = 0;
			}

			requestAnimationFrame(scroll);
		}

		// Start animation
		requestAnimationFrame(scroll);
	}
}

// Initialize marquee when DOM is loaded
document.addEventListener("DOMContentLoaded", initMarquee);
