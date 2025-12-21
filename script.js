document.addEventListener("DOMContentLoaded", () => {
  // --- Helper: API Call ---
  async function apiCall(endpoint, method = "GET", data = null, useJson = false) {
    const options = {
      method: method,
      headers: useJson
        ? { "Content-Type": "application/json" }
        : { "Content-Type": "application/x-www-form-urlencoded" },
    }
    if (data) {
      options.body = useJson ? JSON.stringify(data) : new URLSearchParams(data).toString()
    }
    try {
      const response = await fetch(endpoint, options)
      const contentType = response.headers.get("content-type") || ""
      if (contentType.includes("application/json")) {
        return response.json()
      }
      const textResponse = await response.text()
      console.error(`API Error on ${endpoint}: Non-JSON Response. Text:`, textResponse)
      return { success: false, message: `Server returned non-JSON response from ${endpoint}.` }
    } catch (error) {
      console.error("API Error:", error)
      return { success: false, message: "Network Error." }
    }
  }

  window.apiCall = apiCall

  async function checkAuthenticationStatus() {
    try {
      const result = await apiCall("api/check_session.php")
      console.log("[v0] Auth check result:", result)

      const currentPage = window.location.pathname.split("/").pop() || "index.html"

      if (result && result.isLoggedIn) {
        updateNavigationForRole(result.role, currentPage)

        // Load role-specific content
        if (window.location.pathname.includes("profile.html") && result.role === "student") {
          loadStudentProfile()
          loadStudentInquiries()
          loadStudentContactMessages()
          loadStudentDocuments() // Added call to load documents
        } else if (window.location.pathname.includes("admin_dashboard.html") && result.role === "admin") {
          loadContactMessages()
          loadInquiries()
          loadAllDocuments() // This should now call loadDocuments correctly for admin
        }
      } else {
        // User not logged in
        updateNavigationForRole(null, currentPage)
      }

      if (window.location.pathname.includes("announcement.html")) {
        loadAnnouncements()
      }

      if (window.location.pathname.includes("support.html")) {
        loadFAQs()
        handleSupportPageAuth(result)
      }

      if (window.location.pathname.includes("contact.html")) {
        handleContactPageAuth(result)
      }
    } catch (error) {
      console.error("[v0] Auth check error:", error)
      const currentPage = window.location.pathname.split("/").pop() || "index.html"
      updateNavigationForRole(null, currentPage)

      if (window.location.pathname.includes("announcement.html")) {
        loadAnnouncements()
      }
      if (window.location.pathname.includes("support.html")) {
        loadFAQs()
        handleSupportPageAuth(null)
      }
      if (window.location.pathname.includes("contact.html")) {
        handleContactPageAuth(null)
      }
    }
  }

  function handleSupportPageAuth(authResult) {
    const inquiryWrapper = document.getElementById("inquiry-submission-wrapper")
    const loginPrompt = document.getElementById("login-prompt-wrapper")

    if (!inquiryWrapper || !loginPrompt) return

    if (authResult && authResult.isLoggedIn && authResult.role === "student") {
      // Show inquiry form for logged-in students
      inquiryWrapper.style.display = "block"
      loginPrompt.style.display = "none"
    } else {
      // Show login prompt for non-authenticated users
      inquiryWrapper.style.display = "none"
      loginPrompt.style.display = "block"
    }
  }

  function handleContactPageAuth(authResult) {
    console.log("[v0] handleContactPageAuth called with:", authResult)
    console.log("[v0] authResult.isLoggedIn:", authResult?.isLoggedIn)
    console.log("[v0] authResult.role:", authResult?.role)

    const contactForm = document.getElementById("contact-form")
    const messageDiv = document.getElementById("contact-form-message")

    if (contactForm && messageDiv) {
      if (authResult && authResult.isLoggedIn) {
        // User is logged in - clear any error message
        console.log("[v0] User is logged in - clearing error message")
        messageDiv.innerHTML = ""
        messageDiv.style.display = "none" // Hide the message div completely when logged in
      } else {
        // User is not logged in - show error message
        console.log("[v0] User is NOT logged in - showing error message")
        messageDiv.style.display = "block" // Show the message div when not logged in
        messageDiv.innerHTML =
          '<p style="color: red;">✗ You must be logged in to send a message. <a href="login.html" style="text-decoration: underline;">Log in here</a>.</p>'
      }
    }
  }

  function updateNavigationForRole(role, currentPage) {
    console.log("[v0] updateNavigationForRole called with role:", role, "page:", currentPage)
    const nav = document.querySelector("nav ul")
    if (!nav) {
      console.log("[v0] ERROR: No nav ul element found!")
      return
    }

    console.log("[v0] Found nav element, updating...")

    // Clear existing navigation
    nav.innerHTML = ""

    if (role === "admin") {
      // Admin navigation: all public links + Dashboard, no "My Portal"
      nav.innerHTML = `
        <li><a href="index.html" ${currentPage === "index.html" ? 'class="active"' : ""}>Home</a></li>
        <li><a href="announcement.html" ${currentPage === "announcement.html" ? 'class="active"' : ""}>Announcement</a></li>
        <li><a href="guides.html" ${currentPage === "guides.html" ? 'class="active"' : ""}>Guides & Forms</a></li>
        <li><a href="support.html" ${currentPage === "support.html" ? 'class="active"' : ""}>Support</a></li>
        <li><a href="contact.html" ${currentPage === "contact.html" ? 'class="active"' : ""}>Contact</a></li>
        <li><a href="admin_dashboard.html" ${currentPage.includes("admin_") ? 'class="active"' : ""} style="color: #FFD700; font-weight: bold;">Dashboard</a></li>
        <li class="auth-li"><a href="#" id="logout-link" style="color: #FFD700;">Log Out (admin)</a></li>
      `
      console.log("[v0] Admin navigation rendered")
    } else if (role === "student") {
      // Student navigation: all public links + My Portal, no Dashboard
      nav.innerHTML = `
        <li><a href="index.html" ${currentPage === "index.html" ? 'class="active"' : ""}>Home</a></li>
        <li><a href="announcement.html" ${currentPage === "announcement.html" ? 'class="active"' : ""}>Announcement</a></li>
        <li><a href="guides.html" ${currentPage === "guides.html" ? 'class="active"' : ""}>Guides & Forms</a></li>
        <li><a href="support.html" ${currentPage === "support.html" ? 'class="active"' : ""}>Support</a></li>
        <li><a href="contact.html" ${currentPage === "contact.html" ? 'class="active"' : ""}>Contact</a></li>
        <li><a href="profile.html" ${currentPage === "profile.html" ? 'class="active"' : ""} style="color: #FFD700; font-weight: bold;">My Portal</a></li>
        <li class="auth-li"><a href="#" id="logout-link">Log Out</a></li>
      `
      console.log("[v0] Student navigation rendered")
    } else {
      // PUBLIC NAVIGATION (not logged in)
      nav.innerHTML = `
        <li><a href="index.html" ${currentPage === "index.html" ? 'class="active"' : ""}>Home</a></li>
        <li><a href="announcement.html" ${currentPage === "announcement.html" ? 'class="active"' : ""}>Announcement</a></li>
        <li><a href="guides.html" ${currentPage === "guides.html" ? 'class="active"' : ""}>Guides & Forms</a></li>
        <li><a href="support.html" ${currentPage === "support.html" ? 'class="active"' : ""}>Support</a></li>
        <li><a href="contact.html" ${currentPage === "contact.html" ? 'class="active"' : ""}>Contact</a></li>
        <li class="login-button"><a href="login.html" id="auth-link">Log In</a></li>
      `
      console.log("[v0] Public navigation rendered")
    }
  }

  async function loadAnnouncements() {
    const container = document.getElementById("announcements-container")
    const linksContainer = document.getElementById("links-container")

    if (!container && !linksContainer) {
      return
    }

    if (container) {
      container.innerHTML = '<p style="text-align: center;">Loading announcements...</p>'
    }

    try {
      const result = await apiCall("api/fetch_announcements.php")

      if (result && result.success && result.announcements) {
        const announcements = result.announcements

        const current = announcements.filter((a) => {
          const isArchived = Number.parseInt(a.IsArchived)
          return isArchived === 0 || !isArchived
        })
        const old = announcements.filter((a) => {
          const isArchived = Number.parseInt(a.IsArchived)
          return isArchived === 1
        })

        // Check if user is admin
        const authData = sessionStorage.getItem("auth")
        const isAdmin = authData && JSON.parse(authData).role === "admin"

        const createCard = document.getElementById("create-announcement-card")
        if (createCard) {
          createCard.style.display = isAdmin ? "flex" : "none"
        }

        if (container) {
          if (current.length === 0) {
            container.innerHTML =
              '<p style="text-align: center; color: #666;">No current announcements at this time.</p>'
          } else {
            container.innerHTML = current
              .map((ann) => {
                // Check if there's a valid URL (not empty, not "#", and not just whitespace)
                const hasValidUrl = ann.ContentUrl && ann.ContentUrl.trim() !== "" && ann.ContentUrl.trim() !== "#"

                return `
                  <div class="update-card event" data-id="${ann.AnnouncementID}">
                    ${
                      isAdmin
                        ? `
                      <div class="admin-actions" style="display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 10px;">
                        <button onclick="archiveAnnouncement(${ann.AnnouncementID})" class="archive-btn" title="Archive" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #666;">📦</button>
                        <button onclick="editAnnouncement(${ann.AnnouncementID})" class="edit-btn" title="Edit" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #666; display: inline;">✎</button>
                        <button onclick="deleteAnnouncement(${ann.AnnouncementID})" class="delete-btn" title="Delete" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #cc0000;">🗑</button>
                      </div>
                    `
                        : ""
                    }
                    <h4>${ann.Title || "Untitled"}</h4>
                    <div class="date">${ann.PostedDate || "Unknown date"}</div>
                    <p>${ann.Content || ""}</p>
                    ${hasValidUrl ? `<a href="${ann.ContentUrl}" target="_blank">Learn More →</a>` : ""}
                  </div>
                `
              })
              .join("")
          }
        }

        if (linksContainer) {
          if (old.length === 0) {
            linksContainer.innerHTML = '<p style="text-align: center; color: #666;">No archived announcements.</p>'
          } else {
            linksContainer.innerHTML = old
              .map((ann) => {
                return `
                  <div class="old-announcement-card">
                    <div class="old-announcement-header">
                      <h4>${ann.Title || "Untitled"}</h4>
                      ${
                        isAdmin
                          ? `
                        <button onclick="deleteAnnouncement(${ann.AnnouncementID})" class="delete-btn-small" title="Delete">🗑</button>
                      `
                          : ""
                      }
                    </div>
                    <div class="old-announcement-date">${ann.PostedDate || "Unknown date"}</div>
                    <a href="#" onclick="viewOldAnnouncement(${ann.AnnouncementID}, '${(ann.Title || "").replace(/'/g, "\\'")}', '${ann.PostedDate || ""}', '${(ann.Content || "").replace(/'/g, "\\'").replace(/\n/g, " ")}'); return false;" class="view-archive-link">View Archive</a>
                  </div>
                `
              })
              .join("")
          }
        }
      } else {
        if (container) {
          container.innerHTML =
            '<p style="text-align: center; color: #dc3545;">Failed to load announcements. Please try again later.</p>'
        }
        if (linksContainer) {
          linksContainer.innerHTML =
            '<p style="text-align: center; color: #dc3545;">Failed to load archived announcements.</p>'
        }
      }
    } catch (error) {
      console.error("Error loading announcements:", error)
      if (container) {
        container.innerHTML =
          '<p style="text-align: center; color: #dc3545;">Failed to load announcements. Please try again later.</p>'
      }
      if (linksContainer) {
        linksContainer.innerHTML =
          '<p style="text-align: center; color: #dc3545;">Failed to load archived announcements.</p>'
      }
    }
  }

  // UPDATED FUNCTION FOR FAQ LOADING
  async function loadFAQs() {
    const generalContainer = document.getElementById("general-faq-container")
    const visaContainer = document.getElementById("visa-faq-container")
    const visaTitle = document.getElementById("visa-title")

    if (!generalContainer) return

    generalContainer.innerHTML = "<p>Loading FAQs...</p>"
    if (visaContainer) visaContainer.innerHTML = ""

    const result = await apiCall("api/fetch_faqs.php")

    // Check session for admin role
    const sessionResult = await apiCall("api/check_session.php")
    const isAdmin = sessionResult && sessionResult.isLoggedIn && sessionResult.role === "admin"

    if (result && result.success && result.data) {
      const faqs = result.data

      const generalFaqs = faqs.filter((faq) => faq.category !== "Visa")
      const visaFaqs = faqs.filter((faq) => faq.category === "Visa")

      if (generalFaqs.length > 0) {
        generalContainer.innerHTML = generalFaqs
          .map(
            (faq, index) => `
          <div class="faq-item-card">
            <div class="faq-question-row" onclick="toggleFAQ('general-${index}')">
              <span class="faq-question-text">${faq.question}</span>
              <div class="faq-icons">
                ${isAdmin ? `<button class="faq-edit-icon" onclick="event.stopPropagation(); editFAQ(${faq.faq_id})" title="Edit FAQ">✏️</button>` : ""}
                ${isAdmin ? `<button class="faq-delete-icon" onclick="event.stopPropagation(); deleteFAQ(${faq.faq_id})" title="Delete FAQ">🗑️</button>` : ""}
                <span class="faq-toggle-icon">+</span>
              </div>
            </div>
            <div class="faq-answer-content" id="faq-general-${index}" style="display: none;">
              <p>${faq.answer}</p>
            </div>
          </div>
        `,
          )
          .join("")
      } else {
        generalContainer.innerHTML = "<p>No FAQs available.</p>"
      }

      if (visaFaqs.length > 0 && visaContainer) {
        if (visaTitle) visaTitle.style.display = "block"
        visaContainer.innerHTML = visaFaqs
          .map(
            (faq, index) => `
          <div class="faq-item-card">
            <div class="faq-question-row" onclick="toggleFAQ('visa-${index}')">
              <span class="faq-question-text">${faq.question}</span>
              <div class="faq-icons">
                ${isAdmin ? `<button class="faq-edit-icon" onclick="event.stopPropagation(); editFAQ(${faq.faq_id})" title="Edit FAQ">✏️</button>` : ""}
                ${isAdmin ? `<button class="faq-delete-icon" onclick="event.stopPropagation(); deleteFAQ(${faq.faq_id})" title="Delete FAQ">🗑️</button>` : ""}
                <span class="faq-toggle-icon">+</span>
              </div>
            </div>
            <div class="faq-answer-content" id="faq-visa-${index}" style="display: none;">
              <p>${faq.answer}</p>
            </div>
          </div>
        `,
          )
          .join("")
      }

      if (isAdmin && generalContainer) {
        const existingButton = document.querySelector(".add-faq-button")
        if (!existingButton) {
          generalContainer.insertAdjacentHTML(
            "beforebegin",
            '<button class="cta-button add-faq-button" onclick="showAddFAQModal()" style="margin-bottom: 20px;">+ Add New FAQ</button>',
          )
        }
      }
    } else {
      generalContainer.innerHTML = "<p>Error loading FAQs.</p>"
    }
  }

  // UPDATED toggle function for new FAQ structure
  window.toggleFAQ = (id) => {
    const answer = document.getElementById(`faq-${id}`)
    const toggle = document.querySelector(`[onclick="toggleFAQ('${id}')"] .faq-toggle-icon`)

    if (answer) {
      const isVisible = answer.style.display === "block"
      answer.style.display = isVisible ? "none" : "block"
      if (toggle) {
        toggle.textContent = isVisible ? "+" : "−"
      }
    }
  }

  async function fetchAndRenderStudentList() {
    // Implementation for fetching student list
  }

  async function fetchAndRenderReportData() {
    // Implementation for fetching report data
  }

  const loginForm = document.getElementById("loginForm") || document.querySelector(".login-form")
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      const formData = new FormData(loginForm)
      const data = Object.fromEntries(formData.entries())

      const result = await apiCall("api/login.php", "POST", data)

      if (result.success) {
        // Store auth status in session storage for admin checks in loadAnnouncements
        sessionStorage.setItem("auth", JSON.stringify({ isLoggedIn: true, role: result.role }))
        // Redirect based on role
        if (result.role === "admin") {
          window.location.href = "admin_dashboard.html"
        } else if (result.role === "student") {
          window.location.href = "profile.html"
        } else {
          window.location.href = "index.html"
        }
      } else {
        alert(result.message || "Login failed.")
      }
    })
  }

  document.addEventListener("click", async (e) => {
    if (e.target && (e.target.id === "logout-link" || e.target.textContent.includes("Log Out"))) {
      e.preventDefault()
      await apiCall("api/logout.php")
      sessionStorage.removeItem("auth") // Clear auth from session storage
      window.location.href = "index.html"
    }
  })

  function loadAdminDashboard() {
    console.log("[v0] Loading admin dashboard data")
    // Load inquiry data for dashboard
    const refreshBtn = document.getElementById("refresh-inquiries")
    if (refreshBtn) {
      refreshBtn.addEventListener("click", loadInquiries)
      loadInquiries()
    }

    const refreshContactMessagesBtn = document.getElementById("refresh-contact-messages")
    if (refreshContactMessagesBtn) {
      refreshContactMessagesBtn.addEventListener("click", loadContactMessages)
      loadContactMessages()
    }

    const refreshDocumentsBtn = document.getElementById("refresh-documents")
    if (refreshDocumentsBtn) {
      refreshDocumentsBtn.addEventListener("click", loadAllDocuments) // This now correctly refers to the admin document loading function
      loadAllDocuments() // Load admin documents on dashboard initialization
    }
  }

  async function loadContactMessages() {
    console.log("[v0] Loading contact messages")
    const tbody = document.getElementById("contact-messages-table-body")
    const summary = document.getElementById("contact-messages-summary")

    if (!tbody) return

    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Loading...</td></tr>'

    const result = await apiCall("api/fetch_contact_messages.php")

    console.log("[v0] Contact messages result:", result)

    if (result && result.success && result.messages) {
      const messages = result.messages
      const counts = result.counts

      if (summary) {
        summary.textContent = `Total Messages: ${counts.total_count} | New: ${counts.new_count} | Read: ${counts.read_count} | Responded: ${counts.responded_count} | Archived: ${counts.archived_count}`
      }

      if (messages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No contact messages found.</td></tr>'
      } else {
        tbody.innerHTML = messages
          .map(
            (msg) => `
          <tr>
            <td>${msg.MessageID}</td>
            <td>${msg.FullName}</td>
            <td>${msg.Email}</td>
            <td>${msg.Subject}</td>
            <td>${msg.FormattedDate}</td>
            <td class="status-${msg.Status}">${msg.Status.toUpperCase()}</td>
            <td>
              <button class="small-button" onclick="viewContactMessage(${msg.MessageID}, '${msg.FullName.replace(/'/g, "\\'")}', '${msg.Email}', '${msg.Subject.replace(/'/g, "\\'")}', '${msg.MessageText.replace(/'/g, "\\'").replace(/\n/g, "\\n")}', '${msg.Status}')">View</button>
            </td>
          </tr>
        `,
          )
          .join("")
      }
    } else {
      tbody.innerHTML =
        '<tr><td colspan="7" style="text-align: center; color: red;">Error loading contact messages.</td></tr>'
      console.error("[v0] Error loading contact messages:", result)
    }
  }

  window.viewContactMessage = (messageId, name, email, subject, message, status) => {
    const modal = document.createElement("div")
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    `

    const content = document.createElement("div")
    content.style.cssText = `
      background: white;
      padding: 30px;
      border-radius: 8px;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `

    content.innerHTML = `
      <h2 style="margin-top: 0; color: #8B1A1A;">Contact Message #${messageId}</h2>
      <div style="margin: 20px 0;">
        <p><strong>From:</strong> ${name}</p>
        <p><strong>Email:</strong> <a href="mailto:${email}">${email}</a></p>
        <p><strong>Subject:</strong> ${subject}</p>
        <p><strong>Status:</strong> <span class="status-${status}">${status.toUpperCase()}</span></p>
        <hr style="margin: 20px 0;">
        <p><strong>Message:</strong></p>
        <p style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 4px;">${message}</p>
      </div>
      <div style="margin-top: 20px;">
        <label for="status-select"><strong>Update Status:</strong></label>
        <select id="status-select" style="margin: 10px 0; padding: 8px; width: 100%; border: 1px solid #ddd; border-radius: 4px;">
          <option value="new" ${status === "new" ? "selected" : ""}>New</option>
          <option value="read" ${status === "read" ? "selected" : ""}>Read</option>
          <option value="responded" ${status === "responded" ? "selected" : ""}>Responded</option>
          <option value="archived" ${status === "archived" ? "selected" : ""}>Archived</option>
        </select>
        
        <label for="response-text" style="display: block; margin-top: 15px;"><strong>Your Response to Student:</strong></label>
        <textarea id="response-text" 
          style="width: 100%; min-height: 120px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px; font-family: inherit; resize: vertical;"
          placeholder="Type your response to the student here..."
        ></textarea>
        
        <button id="update-status-btn" class="cta-button" style="margin-top: 15px; margin-right: 10px;">Submit Response</button>
        <button id="close-modal-btn" class="secondary-cta" style="margin-top: 15px;">Closed</button>
      </div>
      <div id="message-feedback" style="margin-top: 15px;"></div>
    `

    modal.appendChild(content)
    document.body.appendChild(modal)

    document.getElementById("update-status-btn").addEventListener("click", async () => {
      const newStatus = document.getElementById("status-select").value
      const responseText = document.getElementById("response-text").value.trim()
      const feedback = document.getElementById("message-feedback")

      feedback.innerHTML = '<p style="color: #666;">Submitting response...</p>'

      const result = await apiCall(
        "api/update_contact_message_status.php",
        "POST",
        {
          messageId: messageId,
          status: newStatus,
          response: responseText,
        },
        true,
      )

      if (result && result.success) {
        feedback.innerHTML = '<p style="color: green;">Response submitted successfully!</p>'
        setTimeout(() => {
          modal.remove()
          loadContactMessages()
        }, 1000)
      } else {
        feedback.innerHTML = '<p style="color: red;">Error submitting response. Please try again.</p>'
      }
    })

    // Close modal handlers
    document.getElementById("close-modal-btn").addEventListener("click", () => modal.remove())
    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.remove()
    })
  }

  async function loadInquiries() {
    console.log("[v0] Loading inquiries")
    const tbody = document.getElementById("inquiry-list-table-body")
    const summary = document.getElementById("inquiry-summary")

    if (!tbody) return

    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading...</td></tr>'

    const result = await apiCall("api/fetch_inquiries.php")

    console.log("[v0] Inquiries result:", result)

    if (result && result.success && result.inquiries) {
      const inquiries = result.inquiries

      if (summary) {
        const pending = inquiries.filter((i) => i.Status === "pending").length
        const inProgress = inquiries.filter((i) => i.Status === "in_progress").length
        const resolved = inquiries.filter((i) => i.Status === "resolved").length
        summary.textContent = `Total Inquiries: ${inquiries.length} | Pending: ${pending} | In Progress: ${inProgress} | Resolved: ${resolved}`
      }

      if (inquiries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No inquiries found.</td></tr>'
      } else {
        tbody.innerHTML = inquiries
          .map(
            (inq) => `
          <tr>
            <td>${inq.InquiryID}</td>
            <td>${inq.StudentName || inq.Username || "N/A"}</td>
            <td>${inq.Subject}</td>
            <td>${inq.SubmittedOn}</td>
            <td class="status-${inq.Status.toLowerCase().replace("_", "-")}">${inq.Status.replace("_", " ").toUpperCase()}</td>
            <td><button class="small-button" onclick="viewInquiry(${inq.InquiryID})">View</button></td>
          </tr>
        `,
          )
          .join("")
      }
    } else {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading inquiries.</td></tr>'
      console.error("[v0] Error loading inquiries:", result)
    }
  }

  // Declare the missing functions
  function renderInquirySubmission(isLoggedIn, role) {
    const wrapper = document.getElementById("inquiry-submission-wrapper")
    if (!wrapper) return
    if (isLoggedIn && role === "student") {
      wrapper.innerHTML = `<form id="inquiry-form" onsubmit="handleInquirySubmit(event)">
          <input type="text" name="subject" placeholder="Subject" required class="form-control mb-2">
          <textarea name="description" placeholder="Message" required class="form-control mb-2"></textarea>
          <button type="submit" class="cta-button">Send</button>
      </form>`
    } else {
      wrapper.innerHTML = '<p><a href="login.html">Log in</a> to submit a ticket.</p>'
    }
  }

  const inquiryForm = document.getElementById("inquiry-form")
  if (inquiryForm) {
    inquiryForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      const formData = new FormData(inquiryForm)
      const data = Object.fromEntries(formData.entries())

      const messageDiv = document.getElementById("form-message")
      messageDiv.innerHTML = '<p style="color: #666;">Submitting inquiry...</p>'

      const result = await apiCall("api/submit_inquiry.php", "POST", data)

      if (result && result.success) {
        messageDiv.innerHTML =
          '<p style="color: green;">✓ ' + (result.message || "Inquiry submitted successfully!") + "</p>"
        inquiryForm.reset()
      } else {
        messageDiv.innerHTML = `<p style="color: red;">✗ ${result.message || "Error submitting inquiry. Please check your Student ID and try again."}</p>`
      }
    })
  }

  const contactForm = document.getElementById("contact-form")
  if (contactForm) {
    console.log("[v0] Contact form found, attaching submit handler")

    const submitButton = contactForm.querySelector('button[type="submit"]')
    if (submitButton) {
      console.log("[v0] Submit button found")
      submitButton.addEventListener("click", (e) => {
        console.log("[v0] Submit button clicked")
      })
    } else {
      console.log("[v0] WARNING: Submit button not found!")
    }

    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      console.log("[v0] Contact form submitted")

      try {
        console.log("[v0] Starting authentication check for form submission...")
        const authStatus = await apiCall("api/check_session.php")
        console.log("[v0] Auth status received for submission:", authStatus)
        console.log("[v0] isLoggedIn:", authStatus?.isLoggedIn)
        console.log("[v0] user_id:", authStatus?.user_id)
        console.log("[v0] role:", authStatus?.role)

        const messageDiv = document.getElementById("contact-form-message")
        if (!messageDiv) {
          console.error("[v0] Message div not found!")
          alert("Error: Message container not found on page")
          return
        }

        if (!authStatus || !authStatus.isLoggedIn) {
          console.log("[v0] Authentication failed - showing error message")
          messageDiv.style.display = "block" // Show error message div
          messageDiv.innerHTML =
            '<p style="color: red;">✗ You must be logged in to send a message. <a href="login.html" style="text-decoration: underline;">Log in here</a>.</p>'
          return
        }

        console.log("[v0] Authentication successful - proceeding with form submission")
        const formData = new FormData(contactForm)
        const data = Object.fromEntries(formData.entries())

        messageDiv.style.display = "block" // Show processing message
        messageDiv.innerHTML = '<p style="color: #666;">Sending message...</p>'

        const result = await apiCall("api/submit_contact_form.php", "POST", data)
        console.log("[v0] Contact form submission result:", result)

        if (result && result.success) {
          messageDiv.innerHTML =
            '<p style="color: green;">✓ ' + (result.message || "Message sent successfully!") + "</p>"
          contactForm.reset()
        } else {
          messageDiv.innerHTML = `<p style="color: red;">✗ ${result.message || "Error sending message. Please try again."}</p>`
        }
      } catch (error) {
        console.error("[v0] Error in contact form submission:", error)
        const messageDiv = document.getElementById("contact-form-message")
        if (messageDiv) {
          messageDiv.innerHTML = '<p style="color: red;">✗ An error occurred. Please try again.</p>'
        }
      }
    })
  } else {
    console.log("[v0] Contact form NOT found on this page")
  }

  async function loadStudentInquiries() {
    console.log("[v0] Loading student inquiries for profile")
    const tbody = document.getElementById("inquiry-history-body")

    if (!tbody) return

    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading your inquiries...</td></tr>'

    // First check if user is logged in as a student
    const sessionResult = await apiCall("api/check_session.php")

    if (!sessionResult || !sessionResult.isLoggedIn || sessionResult.role !== "student") {
      tbody.innerHTML =
        '<tr><td colspan="5" style="text-align: center;">Please log in to view your inquiries.</td></tr>'
      return
    }

    // Fetch student's inquiries
    const result = await apiCall("api/fetch_inquiries.php")

    console.log("[v0] Student inquiries result:", result)

    if (result && result.success && result.inquiries) {
      const inquiries = result.inquiries

      if (inquiries.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="5" style="text-align: center;">You have no inquiries yet. <a href="support.html">Submit your first inquiry</a></td></tr>'
      } else {
        tbody.innerHTML = inquiries
          .map(
            (inq) => `
          <tr>
            <td>#${String(inq.InquiryID).padStart(5, "0")}</td>
            <td>${inq.Subject}</td>
            <td>${inq.SubmittedOn || inq.CreatedAt || "N/A"}</td>
            <td class="status-${(inq.Status || "pending").toLowerCase().replace("_", "-")}">${(inq.Status || "pending").replace("_", " ").toUpperCase()}</td>
            <td><button class="small-button view-response-btn" data-inquiry-id="${inq.InquiryID}" onclick="viewInquiryResponse(${inq.InquiryID})">View</button></td>
          </tr>
        `,
          )
          .join("")
      }
    } else {
      tbody.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: red;">Error loading inquiries. Please try again later.</td></tr>'
      console.error("[v0] Error loading student inquiries:", result)
    }
  }

  window.viewInquiryResponse = async (inquiryId) => {
    console.log("[v0] Opening student inquiry response modal for ID:", inquiryId)

    const modal = document.getElementById("student-inquiry-response-modal")

    if (!modal) {
      console.error("[v0] Student inquiry response modal not found")
      alert("Unable to display inquiry response. Please refresh the page.")
      return
    }

    // Fetch inquiry details
    const result = await apiCall(`api/fetch_inquiry_detail.php?inquiry_id=${inquiryId}`)

    console.log("[v0] Student inquiry detail result:", result)

    if (result && result.success && result.inquiry) {
      const inq = result.inquiry

      // Populate modal fields
      document.getElementById("student-modal-inquiry-id").textContent = "#" + String(inq.InquiryID).padStart(5, "0")
      document.getElementById("student-modal-submitted-date").textContent = inq.SubmittedOn || "N/A"
      document.getElementById("student-modal-subject").textContent = inq.Subject || "N/A"
      document.getElementById("student-modal-description").textContent = inq.Description || "N/A"

      // Set status badge
      const statusSpan = document.getElementById("student-modal-status")
      statusSpan.textContent = inq.Status.replace("_", " ").toUpperCase()
      statusSpan.className = `status-badge status-${inq.Status.toLowerCase().replace("_", "-")}`

      // Show response if available
      const responseText = document.getElementById("student-response-text")
      const responseContent = document.getElementById("student-response-content")

      if (inq.Response && inq.Response.trim() !== "") {
        responseText.textContent = inq.Response
        responseContent.style.background = "#f0f8ff"
      } else {
        responseText.textContent = "No response yet. An ISSO staff member will respond to your inquiry soon."
        responseContent.style.background = "#fff3cd"
      }

      // Show modal
      modal.style.display = "flex"
    } else {
      alert("Failed to load inquiry details: " + (result.message || "Unknown error"))
    }
  }

  // ADDED FUNCTION TO CLOSE STUDENT INQUIRY RESPONSE MODAL
  window.closeStudentInquiryModal = () => {
    const modal = document.getElementById("student-inquiry-response-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  async function loadStudentContactMessages() {
    console.log("[v0] === LOADING STUDENT CONTACT MESSAGES ===")
    const tbody = document.getElementById("contact-message-history-body")

    if (!tbody) {
      console.error("[v0] ERROR: contact-message-history-body element not found!")
      return
    }

    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading your messages...</td></tr>'

    // Check if user is logged in as a student
    console.log("[v0] Step 1: Checking session...")
    const sessionResult = await apiCall("api/check_session.php")
    console.log("[v0] Session result:", sessionResult)

    if (!sessionResult || !sessionResult.isLoggedIn || sessionResult.role !== "student") {
      console.error("[v0] ERROR: Not logged in as student", sessionResult)
      tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Please log in to view your messages.</td></tr>'
      return
    }

    console.log("[v0] Step 2: Fetching contact messages...")
    // Fetch student's contact messages
    const result = await apiCall("api/fetch_student_contact_messages.php")

    console.log("[v0] === RAW API RESPONSE ===")
    console.log("[v0] Full result object:", JSON.stringify(result, null, 2))
    console.log("[v0] Result success:", result?.success)
    console.log("[v0] Result messages:", result?.messages)
    console.log("[v0] Messages length:", result?.messages?.length)

    if (result && result.debug_info) {
      console.log("[v0] === DEBUG INFO ===")
      console.log("[v0] Your logged-in student account email:", result.debug_info.logged_in_email)
      console.log("[v0] User ID:", result.debug_info.user_id)
      console.log("[v0] Messages found:", result.debug_info.message_count)
      console.log("[v0] Query used:", result.debug_info.query_used)
      console.log("[v0] === IMPORTANT ===")
      console.log(
        "[v0] Check your database CONTACT_MESSAGE table for entries with email:",
        result.debug_info.logged_in_email,
      )

      if (result.debug_info.message_count === 0) {
        const alertDiv = document.createElement("div")
        alertDiv.style.cssText = `
          background: #fff3cd;
          border: 2px solid #ffc107;
          padding: 15px;
          margin: 15px 0;
          border-radius: 8px;
          color: #856404;
        `
        alertDiv.innerHTML = `
          <h4 style="margin-top: 0; color: #8B1A1A;">⚠️ No Messages Found - Email Mismatch</h4>
          <p><strong>Your logged-in student account email:</strong> ${result.debug_info.logged_in_email}</p>
          <p><strong>Problem:</strong> When you submit a contact form, you need to use the exact same email address as your student account.</p>
          <p><strong>Solution:</strong> Go to the <a href="contact.html">Contact page</a> and submit messages using the email: <strong>${result.debug_info.logged_in_email}</strong></p>
          <p style="margin-bottom: 0; font-size: 0.9em;">Check your database CONTACT_MESSAGE table - the messages with email 'dili@gmail.com' don't match your account email.</p>
        `
        tbody.parentElement.parentElement.insertBefore(alertDiv, tbody.parentElement)
      }
    }

    if (result && result.success && result.messages) {
      const messages = result.messages
      console.log("[v0] Processing", messages.length, "messages")

      if (messages.length === 0) {
        console.warn("[v0] No messages found for this email")
        tbody.innerHTML =
          '<tr><td colspan="5" style="text-align: center;">You have no contact messages yet. <a href="contact.html">Send your first message</a></td></tr>'
      } else {
        console.log("[v0] Rendering", messages.length, "messages to table")
        tbody.innerHTML = messages
          .map(
            (msg) => `
          <tr>
            <td>#${String(msg.MessageID).padStart(5, "0")}</td>
            <td>${msg.Subject}</td>
            <td>${msg.FormattedDate}</td>
            <td class="status-${(msg.Status || "new").toLowerCase()}">${(msg.Status || "new").toUpperCase()}</td>
            <td><button class="small-button" onclick="viewStudentContactMessage(${msg.MessageID}, '${msg.Subject.replace(/'/g, "\\'")}', '${msg.MessageText.replace(/'/g, "\\'").replace(/\n/g, "\\n")}', '${msg.Status}', '${msg.FormattedDate}')">View</button></td>
          </tr>
        `,
          )
          .join("")
        console.log("[v0] Messages rendered successfully!")
      }
    } else {
      console.error("[v0] ERROR: API call failed or returned invalid data")
      console.error("[v0] Result:", result)
      tbody.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: red;">Error loading messages. Check console for details.</td></tr>'
    }
    console.log("[v0] === END LOADING MESSAGES ===")
  }

  window.viewStudentContactMessage = (messageId, subject, message, status, date) => {
    const modal = document.createElement("div")
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    `

    const content = document.createElement("div")
    content.style.cssText = `
      background: white;
      padding: 30px;
      border-radius: 8px;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `

    content.innerHTML = `
      <h2 style="margin-top: 0; color: #8B1A1A;">Contact Message #${String(messageId).padStart(5, "0")}</h2>
      <div style="margin: 20px 0;">
        <p><strong>Subject:</strong> ${subject}</p>
        <p><strong>Date Submitted:</strong> ${date}</p>
        <p><strong>Status:</strong> <span class="status-${status.toLowerCase()}">${status.toUpperCase()}</span></p>
        <hr style="margin: 20px 0;">
        <p><strong>Your Message:</strong></p>
        <p style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 4px;">${message}</p>
        ${
          status === "responded"
            ? '<p style="color: #8B1A1A; margin-top: 20px;"><strong>Note:</strong> ISSO has responded to your message via email. Please check your inbox.</p>'
            : '<p style="color: #666; margin-top: 20px;">Status: ' +
              status.toUpperCase() +
              " - We'll respond to your message soon.</p>"
        }
      </div>
      <button onclick="this.closest('[style*=\\'position: fixed\\']').remove()" style="background: #8B1A1A; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
    `

    modal.appendChild(content)
    document.body.appendChild(modal)

    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.remove()
    })
  }

  // Expose helper function that was renamed
  window.viewDocument = window.viewDocumentForReview

  // ADDED FUNCTIONS FOR ANNOUNCEMENT MANAGEMENT
  // The modal-based versions are defined later in the DOMContentLoaded section and are the correct ones

  // Only keep the modal-based version below

  window.editAnnouncement = (announcementId) => {
    console.log("[v0] editAnnouncement called with ID:", announcementId)
    const modal = document.getElementById("edit-announcement-modal")
    if (!modal) {
      console.error("[v0] Edit modal not found")
      return
    }

    // Fetch announcement data
    fetch(`api/fetch_announcements.php`)
      .then((res) => res.json())
      .then((data) => {
        console.log("[v0] Fetched announcements data:", data)
        if (data.success && data.announcements && data.announcements.length > 0) {
          const announcement = data.announcements.find(
            (ann) => ann.AnnouncementID == announcementId || ann.content_id == announcementId,
          )

          console.log("[v0] Found announcement:", announcement)

          if (announcement) {
            document.getElementById("edit-announcement-id").value =
              announcement.AnnouncementID || announcement.content_id
            document.getElementById("edit-announcement-title").value = announcement.Title || announcement.title || ""
            document.getElementById("edit-announcement-content").value =
              announcement.Content || announcement.content_text || ""
            document.getElementById("edit-announcement-url").value =
              announcement.ContentUrl || announcement.content_url || ""

            // Ensure the modal header is set correctly for editing
            const modalHeader = modal.querySelector(".modal-header h2")
            if (modalHeader) {
              modalHeader.textContent = "Edit Announcement"
            }
            modal.style.display = "block"
          } else {
            console.error("[v0] Announcement not found with ID:", announcementId)
            alert("Failed to load announcement data")
          }
        } else {
          console.error("[v0] No announcements in response")
          alert("Failed to load announcement data")
        }
      })
      .catch((err) => {
        console.error("[v0] Error fetching announcement:", err)
        alert("Failed to load announcement data")
      })
  }

  window.closeEditAnnouncementModal = () => {
    const modal = document.getElementById("edit-announcement-modal")
    if (modal) {
      modal.style.display = "none"
      // Reset form pointer-events to default if it was set to none for viewing
      const form = modal.querySelector("#edit-announcement-form")
      if (form) {
        form.style.pointerEvents = "auto"
        form.style.opacity = "1" // Reset opacity
      }
      // Reset modal title if it was changed for viewing
      const modalHeader = modal.querySelector(".modal-header h2")
      if (modalHeader) {
        modalHeader.textContent = "Edit Announcement" // Default to edit mode title
      }
      // Show submit button again if it was hidden
      const submitBtn = modal.querySelector(".modal-footer button[type='submit']")
      if (submitBtn) {
        submitBtn.style.display = "block"
      }
    }
  }

  // Moved this event listener inside the DOMContentLoaded to ensure the element exists.
  // Also changed to use fetch and JSON body as per modern practices and to match other similar calls.
  const editForm = document.getElementById("edit-announcement-form")
  if (editForm) {
    editForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const announcementId = document.getElementById("edit-announcement-id").value
      const title = document.getElementById("edit-announcement-title").value
      const content = document.getElementById("edit-announcement-content").value
      const url = document.getElementById("edit-announcement-url").value

      console.log("[v0] Submitting announcement:", { announcementId, title, content, url })

      try {
        const response = await fetch("api/update_announcement.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            content_id: announcementId === "" || announcementId === "0" ? null : announcementId,
            title: title,
            content_text: content,
            content_url: url,
          }),
        })

        console.log("[v0] Response status:", response.status)
        console.log("[v0] Response ok:", response.ok)

        const responseText = await response.text()
        console.log("[v0] Response text:", responseText)

        const data = JSON.parse(responseText)
        console.log("[v0] Parsed data:", data)

        if (data.success) {
          const isCreate = announcementId === "" || announcementId === "0"
          alert(isCreate ? "Announcement created successfully" : "Announcement updated successfully")
          window.closeEditAnnouncementModal()
          window.loadAnnouncements()
        } else {
          alert("Error: " + (data.message || "Failed to save announcement"))
        }
      } catch (error) {
        console.error("[v0] Full error:", error)
        console.error("[v0] Error message:", error.message)
        alert("Network error. Failed to save announcement.")
      }
    })
  }

  window.createAnnouncement = () => {
    console.log("[v0] createAnnouncement called")
    const modal = document.getElementById("edit-announcement-modal")
    if (!modal) {
      console.error("Create modal not found")
      return
    }

    // Clear form for new announcement
    document.getElementById("edit-announcement-id").value = "" // Clear ID to signify create mode
    document.getElementById("edit-announcement-title").value = ""
    document.getElementById("edit-announcement-content").value = ""
    document.getElementById("edit-announcement-url").value = ""

    // Make form editable and visible
    const form = modal.querySelector("#edit-announcement-form")
    if (form) {
      form.style.pointerEvents = "auto"
      form.style.opacity = "1"
    }

    // Update modal header to say "Create"
    const modalHeader = modal.querySelector(".modal-header h2")
    if (modalHeader) {
      modalHeader.textContent = "Create New Announcement"
    }

    // Change submit button text
    const submitBtn = modal.querySelector(".modal-footer button[type='submit']")
    if (submitBtn) {
      submitBtn.textContent = "Create Announcement"
      submitBtn.style.display = "block" // Ensure it's visible
    }

    modal.style.display = "block"
  }

  window.deleteAnnouncement = (announcementId) => {
    console.log("[v0] deleteAnnouncement called with ID:", announcementId)
    const modal = document.getElementById("confirm-delete-modal")
    if (!modal) {
      console.error("Delete modal not found")
      return
    }
    document.getElementById("delete-announcement-id").value = announcementId
    modal.style.display = "block"
  }

  window.closeConfirmDeleteModal = () => {
    const modal = document.getElementById("confirm-delete-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  window.confirmDeleteAnnouncement = () => {
    const announcementId = document.getElementById("delete-announcement-id").value
    const formData = new FormData()
    formData.append("content_id", announcementId)

    fetch("api/delete_announcement.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert("Announcement deleted successfully!")
          window.closeConfirmDeleteModal()
          window.loadAnnouncements()
        } else {
          alert("Error deleting announcement: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Delete error:", error)
        alert("Network error. Failed to delete announcement.")
      })
  }

  window.archiveAnnouncement = (announcementId) => {
    const modal = document.getElementById("confirm-archive-modal")
    if (!modal) {
      console.error("Archive modal not found")
      return
    }
    document.getElementById("archive-announcement-id").value = announcementId
    modal.style.display = "block"
  }

  window.closeConfirmArchiveModal = () => {
    const modal = document.getElementById("confirm-archive-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  window.confirmArchiveAnnouncement = () => {
    const announcementId = document.getElementById("archive-announcement-id").value

    const formData = new FormData()
    formData.append("id", announcementId)

    fetch("api/archive_announcement.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert("Announcement archived successfully!")
          window.closeConfirmArchiveModal()
          window.loadAnnouncements()
        } else {
          alert("Error archiving announcement: " + data.message)
        }
      })
      .catch((err) => {
        console.error("Error archiving announcement:", err)
        alert("Failed to archive announcement")
      })
  }

  window.viewOldAnnouncement = (id, title, date, content) => {
    alert(`${title}\n\n${date}\n\n${content}`)
  }

  // Close modals when clicking outside
  document.addEventListener("click", (event) => {
    const editModal = document.getElementById("edit-announcement-modal")
    const deleteModal = document.getElementById("confirm-delete-modal")
    const archiveModal = document.getElementById("confirm-archive-modal")

    // Check if the clicked element is the modal overlay itself
    if (editModal && event.target === editModal) {
      window.closeEditAnnouncementModal()
    }
    if (deleteModal && event.target === deleteModal) {
      window.closeConfirmDeleteModal()
    }
    if (archiveModal && event.target === archiveModal) {
      window.closeConfirmArchiveModal()
    }
  })

  // Moved viewInquiry definition before loadInquiries to ensure it's defined when the onclick handlers are created
  window.viewInquiry = async (inquiryId) => {
    console.log("[v0] Opening admin inquiry detail modal for ID:", inquiryId)

    const modal = document.getElementById("inquiry-detail-modal")

    if (!modal) {
      console.error("[v0] Admin inquiry detail modal not found")
      alert("Unable to display inquiry details. Please refresh the page.")
      return
    }

    // Fetch inquiry details
    const result = await apiCall(`api/fetch_inquiry_detail.php?inquiry_id=${inquiryId}`)

    console.log("[v0] Admin inquiry detail result:", result)

    if (result && result.success && result.inquiry) {
      const inq = result.inquiry

      // Populate modal fields
      document.getElementById("modal-inquiry-id").textContent = "#" + String(inq.InquiryID).padStart(5, "0")
      document.getElementById("modal-student-name").textContent = inq.StudentName || inq.Username || "N/A"
      document.getElementById("modal-student-email").textContent = inq.StudentEmail || "N/A"
      document.getElementById("modal-submitted-date").textContent = inq.SubmittedOn || "N/A"
      document.getElementById("modal-subject").textContent = inq.Subject || "N/A"
      document.getElementById("modal-description").textContent = inq.Description || "N/A"

      // Set status badge
      const statusSpan = document.getElementById("modal-status")
      statusSpan.textContent = inq.Status.replace("_", " ").toUpperCase()
      statusSpan.className = `status-badge status-${inq.Status.toLowerCase().replace("_", "-")}`

      // Set the response form values
      document.getElementById("response-inquiry-id").value = inq.InquiryID
      document.getElementById("response-status").value = inq.Status || "pending"

      // Show existing response if available
      const existingResponseView = document.getElementById("existing-response-view")
      const existingResponseText = document.getElementById("existing-response-text")

      if (inq.Response && inq.Response.trim() !== "") {
        existingResponseText.textContent = inq.Response
        existingResponseView.style.display = "block"
      } else {
        existingResponseView.style.display = "none"
      }

      // Clear the response textarea for new response
      document.getElementById("response-text").value = ""

      // Show modal
      modal.style.display = "flex"
    } else {
      alert("Failed to load inquiry details: " + (result.message || "Unknown error"))
    }
  }

  window.closeInquiryModal = () => {
    const modal = document.getElementById("inquiry-detail-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  const inquiryResponseForm = document.getElementById("inquiry-response-form")
  if (inquiryResponseForm) {
    inquiryResponseForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      const inquiryId = document.getElementById("response-inquiry-id").value
      const status = document.getElementById("response-status").value
      const responseText = document.getElementById("response-text").value.trim()
      const messageDiv = document.getElementById("response-message")

      messageDiv.innerHTML = '<p style="color: #666;">Submitting response...</p>'

      const result = await apiCall(
        "api/update_inquiry_status.php",
        "POST",
        {
          inquiry_id: inquiryId,
          status: status,
          response: responseText,
        },
        false, // Changed from true to false
      )

      if (result && result.success) {
        messageDiv.innerHTML = '<p style="color: green;">Response submitted successfully!</p>'
        setTimeout(() => {
          window.closeInquiryModal()
          loadInquiries()
        }, 1500)
      } else {
        messageDiv.innerHTML =
          '<p style="color: red;">Error: ' + (result.message || "Failed to submit response") + "</p>"
      }
    })
  }

  async function loadStudentDocuments() {
    console.log("[v0] Loading student documents for profile")
    const tbody = document.getElementById("document-status-body")

    if (!tbody) return

    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading your documents...</td></tr>'

    // First check if user is logged in as a student
    const sessionResult = await apiCall("api/check_session.php")

    if (!sessionResult || !sessionResult.isLoggedIn || sessionResult.role !== "student") {
      tbody.innerHTML =
        '<tr><td colspan="4" style="text-align: center;">Please log in to view your documents.</td></tr>'
      return
    }

    // Get student ID
    const studentInfoResult = await apiCall("api/fetch_student_info.php")

    if (!studentInfoResult || !studentInfoResult.success || !studentInfoResult.student_id) {
      tbody.innerHTML =
        '<tr><td colspan="4" style="text-align: center; color: red;">Error loading student information.</td></tr>'
      return
    }

    const studentId = studentInfoResult.student_id

    // Fetch student's documents
    const result = await apiCall(`api/fetch_student_documents.php?student_id=${studentId}`)

    console.log("[v0] Student documents result:", result)

    if (result && result.success && result.data) {
      const documents = result.data

      if (documents.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="4" style="text-align: center;">No documents uploaded yet. Upload your first document above.</td></tr>'
      } else {
        tbody.innerHTML = documents
          .map((doc) => {
            const statusClass = `status-${doc.ReviewStatus.toLowerCase().replace(" ", "-")}`
            return `
          <tr>
            <td>${doc.FileName}</td>
            <td>${doc.FileType}</td>
            <td class="${statusClass}">${doc.ReviewStatus}</td>
            <td>${doc.UploadDateFormatted}</td>
          </tr>
        `
          })
          .join("")
      }
    } else {
      tbody.innerHTML =
        '<tr><td colspan="4" style="text-align: center; color: red;">Error loading documents. Please try again later.</td></tr>'
      console.error("[v0] Error loading student documents:", result)
    }
  }

  const documentUploadForm = document.getElementById("document-upload-form")
  if (documentUploadForm) {
    console.log("[v0] Document upload form found, attaching submit handler")

    documentUploadForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      console.log("[v0] Document upload form submitted")

      const formData = new FormData(documentUploadForm)
      const submitButton = documentUploadForm.querySelector('button[type="submit"]')

      // Validate inputs
      const docType = formData.get("document_type")
      const docFile = formData.get("document_file")

      if (!docType || docType === "") {
        alert("Please select a document type.")
        return
      }

      if (!docFile || docFile.size === 0) {
        alert("Please select a file to upload.")
        return
      }

      // Disable submit button during upload
      if (submitButton) {
        submitButton.disabled = true
        submitButton.textContent = "Uploading..."
      }

      try {
        // Upload using fetch with FormData (not the apiCall helper since it's multipart)
        const response = await fetch("api/upload_document.php", {
          method: "POST",
          body: formData,
        })

        const result = await response.json()
        console.log("[v0] Document upload result:", result)

        if (result && result.success) {
          alert("✓ " + (result.message || "Document uploaded successfully!"))
          documentUploadForm.reset()

          // Reload documents table
          loadStudentDocuments()
        } else {
          alert("✗ " + (result.message || "Error uploading document. Please try again."))
        }
      } catch (error) {
        console.error("[v0] Document upload error:", error)
        alert("✗ Network error. Please try again.")
      } finally {
        // Re-enable submit button
        if (submitButton) {
          submitButton.disabled = false
          submitButton.textContent = "Upload Document"
        }
      }
    })
  }

  // This function is the one intended for admin review of documents
  async function loadAllDocuments() {
    console.log("[v0] Loading documents for admin review...")

    try {
      const response = await fetch("api/fetch_all_documents.php")
      const data = await response.json()

      console.log("[v0] Documents response:", data)

      if (data.success) {
        const tbody = document.querySelector("#documents-table-body") // Ensure this is the correct ID for the admin table
        if (!tbody) {
          console.error("[v0] documents-table-body not found!")
          return
        }
        tbody.innerHTML = ""

        // Update summary
        const summary = document.getElementById("documents-summary") // Assuming this is the ID for the summary
        if (summary && data.summary) {
          summary.textContent = `Total Documents: ${data.summary.total} | Pending: ${data.summary.pending} | Under Review: ${data.summary.under_review} | Approved: ${data.summary.approved} | Rejected: ${data.summary.rejected}`
        }

        if (data.data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No documents uploaded yet.</td></tr>'
          return
        }

        data.data.forEach((doc) => {
          const row = document.createElement("tr")

          // Status styling
          let statusClass = ""
          let statusBgColor = ""
          switch (doc.ReviewStatus) {
            case "Pending":
              statusBgColor = "#FFF3CD"
              statusClass = "status-pending"
              break
            case "Under Review":
              statusBgColor = "#D1ECF1"
              statusClass = "status-under-review"
              break
            case "Approved":
              statusBgColor = "#D4EDDA"
              statusClass = "status-approved"
              break
            case "Rejected":
              statusBgColor = "#F8D7DA"
              statusClass = "status-rejected"
              break
          }

          row.innerHTML = `
                    <td>${doc.DocumentID}</td>
                    <td>${doc.StudentName} (${doc.UniversityID})</td>
                    <td>${doc.FileName}</td>
                    <td>${doc.FileType.toUpperCase()}</td>
                    <td>${doc.UploadDateFormatted}</td>
                    <td><span class="${statusClass}" style="background-color: ${statusBgColor}; padding: 5px 10px; border-radius: 4px; font-weight: bold;">${doc.ReviewStatus}</span></td>
                    <td><button class="small-button" onclick="viewDocumentForReview(${doc.DocumentID})">Review</button></td>
                `

          tbody.appendChild(row)
        })
      } else {
        console.error("[v0] Failed to load documents:", data.message)
        alert(data.message)
      }
    } catch (error) {
      console.error("[v0] Error loading documents:", error)
      alert("Error loading documents. Please try again.")
    }
  }

  window.viewDocumentForReview = (docId) => {
    console.log("[v0] Viewing document for review:", docId)

    try {
      // Fetch all documents again to find the specific one
      fetch("api/fetch_all_documents.php")
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const doc = data.data.find((d) => d.DocumentID == docId)

            if (doc) {
              document.getElementById("modal-document-id").textContent = doc.DocumentID
              document.getElementById("modal-doc-student-name").textContent = `${doc.StudentName} (${doc.StudentEmail})`
              document.getElementById("modal-doc-student-id").textContent = doc.StudentID
              document.getElementById("modal-doc-type").textContent = doc.FileName
              document.getElementById("modal-doc-file-type").textContent = doc.FileType.toUpperCase()
              document.getElementById("modal-doc-upload-date").textContent = doc.UploadDateFormatted
              document.getElementById("modal-doc-file-path").textContent = doc.FilePath || "-"

              // Set status badge
              const statusSpan = document.getElementById("modal-doc-status")
              statusSpan.textContent = doc.ReviewStatus.toUpperCase()
              statusSpan.className = `status-badge status-${doc.ReviewStatus.toLowerCase().replace(" ", "-")}`

              // Set the document ID for the update
              document.getElementById("review-document-id").value = doc.DocumentID

              // Set current status in dropdown
              document.getElementById("review-status").value = doc.ReviewStatus

              // Set review notes if exists
              document.getElementById("review-notes").value = doc.ReviewNotes || ""

              // Show document preview
              const previewContainer = document.getElementById("document-preview-container")
              const filePath = doc.FilePath

              if (doc.FileType.toLowerCase() === "pdf") {
                previewContainer.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="400px" />`
              } else if (["jpg", "jpeg", "png", "gif"].includes(doc.FileType.toLowerCase())) {
                previewContainer.innerHTML = `<img src="${filePath}" alt="Document preview" style="max-width: 100%; height: auto;" />`
              } else {
                previewContainer.innerHTML = `<p>Preview not available for this file type. <a href="${filePath}" target="_blank">Download file</a></p>`
              }

              // Show modal
              document.getElementById("document-review-modal").style.display = "flex"
            } else {
              alert("Document not found.")
            }
          } else {
            alert("Failed to load documents: " + data.message)
          }
        })
    } catch (error) {
      console.error("[v0] Error loading document details:", error)
      alert("Error loading document details. Please try again.")
    }
  }

  window.closeDocumentModal = () => {
    // Renaming to match the modal structure used in viewDocument
    const modal = document.getElementById("document-review-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  const documentReviewForm = document.getElementById("document-review-form")
  if (documentReviewForm) {
    documentReviewForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      console.log("[v0] Form submitted")

      const documentId = document.getElementById("review-document-id")?.value?.trim()
      const reviewStatus = document.getElementById("review-status")?.value?.trim()
      const reviewNotes = document.getElementById("review-notes")?.value?.trim() || ""

      console.log("[v0] Document ID:", documentId)
      console.log("[v0] Review Status:", reviewStatus)
      console.log("[v0] Review Notes:", reviewNotes)

      const messageDiv = document.getElementById("review-message")

      if (!documentId || !reviewStatus) {
        console.log("[v0] Validation failed - documentId:", documentId, "reviewStatus:", reviewStatus)
        messageDiv.innerHTML = '<p style="color: red;">Error: Document ID and review status are required.</p>'
        return
      }

      messageDiv.innerHTML = '<p style="color: #666;">Submitting review...</p>'

      // </CHANGE> Changed from useJson=true to useJson=false to send as form data that PHP $_POST expects
      const result = await apiCall(
        "api/update_document_status.php",
        "POST",
        {
          document_id: documentId,
          review_status: reviewStatus,
          review_notes: reviewNotes,
        },
        false, // Changed from true to false
      )

      if (result && result.success) {
        messageDiv.innerHTML = '<p style="color: green;">Document review submitted successfully!</p>'
        setTimeout(() => {
          window.closeDocumentModal()
          loadAllDocuments()
        }, 1500)
      } else {
        messageDiv.innerHTML =
          '<p style="color: red;">Error: ' + (result.message || "Failed to update document status") + "</p>"
      }
    })
  }

  // Renaming loadDocuments to loadAllDocuments for clarity
  // This function is the one intended for admin review of documents
  // This is a duplicate of the previous loadDocuments function, ensure one is kept.
  // Keeping the latest definition that uses `loadAllDocuments` as intended.

  // Re-expose the helper function that was renamed
  window.viewDocument = window.viewDocumentForReview

  window.showAddFAQModal = () => {
    const modal = document.getElementById("faq-modal")
    if (!modal) {
      console.error("FAQ modal not found")
      return
    }

    // Clear form for new FAQ
    document.getElementById("faq-id").value = ""
    document.getElementById("faq-question").value = ""
    document.getElementById("faq-answer").value = ""
    document.getElementById("faq-category").value = "General"
    document.getElementById("faq-display-order").value = "0"

    // Update modal title
    document.getElementById("faq-modal-title").textContent = "Add New FAQ"

    modal.style.display = "flex"
  }

  window.editFAQ = async (faqId) => {
    const modal = document.getElementById("faq-modal")
    if (!modal) {
      console.error("FAQ modal not found")
      return
    }

    // Fetch FAQ details
    const result = await apiCall("api/fetch_faqs.php")

    if (result && result.success && result.data) {
      const faq = result.data.find((f) => f.faq_id == faqId)

      if (faq) {
        document.getElementById("faq-id").value = faq.faq_id
        document.getElementById("faq-question").value = faq.question
        document.getElementById("faq-answer").value = faq.answer
        document.getElementById("faq-category").value = faq.category || "General"
        document.getElementById("faq-display-order").value = faq.display_order || "0"

        // Update modal title
        document.getElementById("faq-modal-title").textContent = "Edit FAQ"

        modal.style.display = "flex"
      }
    }
  }

  window.closeFAQModal = () => {
    const modal = document.getElementById("faq-modal")
    if (modal) {
      modal.style.display = "none"
    }
  }

  window.deleteFAQ = async (faqId) => {
    if (!confirm("Are you sure you want to delete this FAQ? This action cannot be undone.")) {
      return
    }

    const result = await window.apiCall("api/delete_faq.php", "POST", { faq_id: faqId }, false)

    if (result && result.success) {
      alert("FAQ deleted successfully!")
      loadFAQs()
    } else {
      alert("Error deleting FAQ: " + (result?.message || "Unknown error"))
    }
  }

  // FAQ form submission handler
  const faqForm = document.getElementById("faq-form")
  if (faqForm) {
    faqForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const faqId = document.getElementById("faq-id").value
      const question = document.getElementById("faq-question").value.trim()
      const answer = document.getElementById("faq-answer").value.trim()
      const category = document.getElementById("faq-category").value
      const displayOrder = document.getElementById("faq-display-order").value

      if (!question || !answer) {
        alert("Question and answer are required")
        return
      }

      const isEdit = faqId && faqId !== ""
      const endpoint = isEdit ? "api/update_faq.php" : "api/create_faq.php"

      const data = {
        question: question,
        answer: answer,
        category: category,
        display_order: displayOrder,
      }

      if (isEdit) {
        data.faq_id = faqId
      }

      const result = await apiCall(endpoint, "POST", data, false) // Changed useJson to false

      if (result && result.success) {
        alert(isEdit ? "FAQ updated successfully" : "FAQ created successfully")
        window.closeFAQModal()
        loadFAQs()
      } else {
        alert("Error: " + (result.message || "Failed to save FAQ"))
      }
    })
  }

  async function loadStudentProfile() {
    console.log("[v0] === STARTING loadStudentProfile() ===")
    console.log("[v0] Current URL:", window.location.href)

    const studentNameSpan = document.getElementById("student-name")
    const infoIdSpan = document.getElementById("info-id")
    const infoEmailSpan = document.getElementById("info-email")
    const infoNationalitySpan = document.getElementById("info-nationality")

    console.log("[v0] DOM Elements found:", {
      studentNameSpan: !!studentNameSpan,
      infoIdSpan: !!infoIdSpan,
      infoEmailSpan: !!infoEmailSpan,
      infoNationalitySpan: !!infoNationalitySpan,
    })

    // Set loading state
    if (studentNameSpan) studentNameSpan.textContent = "Loading..."
    if (infoIdSpan) infoIdSpan.textContent = "Loading..."
    if (infoEmailSpan) infoEmailSpan.textContent = "Loading..."
    if (infoNationalitySpan) infoNationalitySpan.textContent = "Loading..."

    console.log("[v0] Making API call to: api/fetch_student_info.php")

    // Fetch student information
    const result = await apiCall("api/fetch_student_info.php")

    console.log("[v0] Student profile API response:", JSON.stringify(result, null, 2))

    if (result && result.success) {
      console.log("[v0] SUCCESS - Updating profile with data")
      // Update student name
      if (studentNameSpan) {
        const fullName = `${result.first_name || ""} ${result.last_name || ""}`.trim()
        console.log("[v0] Setting student name to:", fullName)
        studentNameSpan.textContent = fullName || "Student"
      }

      // Update student info
      if (infoIdSpan) {
        console.log("[v0] Setting student ID to:", result.student_id)
        infoIdSpan.textContent = result.student_id || "N/A"
      }
      if (infoEmailSpan) {
        console.log("[v0] Setting email to:", result.email)
        infoEmailSpan.textContent = result.email || "N/A"
      }
      if (infoNationalitySpan) {
        console.log("[v0] Setting nationality to:", result.nationality)
        infoNationalitySpan.textContent = result.nationality || "N/A"
      }
      console.log("[v0] === loadStudentProfile() COMPLETED SUCCESSFULLY ===")
    } else {
      console.error("[v0] FAILED - Error loading student profile")
      console.error("[v0] Result object:", result)
      console.error("[v0] Error message:", result?.message || "Unknown error")
      if (studentNameSpan) studentNameSpan.textContent = "Student"
      if (infoIdSpan) infoIdSpan.textContent = "N/A"
      if (infoEmailSpan) infoEmailSpan.textContent = "N/A"
      if (infoNationalitySpan) infoNationalitySpan.textContent = "N/A"
      console.log("[v0] === loadStudentProfile() COMPLETED WITH ERRORS ===")
    }
  }

  // --- NEW FUNCTIONS FOR GUIDES & FORMS ---

  async function loadGuides() {
    const container = document.getElementById("guides-container")
    if (!container) return

    container.innerHTML = '<p style="text-align: center;">Loading guides...</p>'

    try {
      const result = await apiCall("api/fetch_guides.php")

      if (result && result.success && result.data) {
        const guides = result.data

        // Check if user is admin
        const authData = sessionStorage.getItem("auth")
        const isAdmin = authData && JSON.parse(authData).role === "admin"

        const createCard = document.getElementById("create-guide-card")
        if (createCard) {
          createCard.style.display = isAdmin ? "flex" : "none"
        }

        if (guides.length === 0) {
          container.innerHTML = '<p style="text-align: center; color: #666;">No guides available at this time.</p>'
        } else {
          container.innerHTML = guides
            .map((guide, index) => {
              const hasValidUrl =
                guide.content_url && guide.content_url.trim() !== "" && guide.content_url.trim() !== "#"

              return `
              <article class="guide-card" data-id="${guide.content_id}">
                ${
                  isAdmin
                    ? `
                  <div class="admin-actions" style="display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 10px;">
                    <button onclick="editGuide(${guide.content_id})" class="edit-btn" title="Edit" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #666;">✎</button>
                    <button onclick="deleteGuide(${guide.content_id})" class="delete-btn" title="Delete" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #cc0000;">🗑</button>
                  </div>
                `
                    : ""
                }
                <h3>${index + 1}. ${guide.title || "Untitled Guide"}</h3>
                <p>${guide.description || ""}</p>
                ${hasValidUrl ? `<a href="${guide.content_url}" target="_blank">Read Guide</a>` : '<a href="#">Read Guide</a>'}
              </article>
            `
            })
            .join("")
        }
      } else {
        container.innerHTML = '<p style="text-align: center; color: #cc0000;">Failed to load guides.</p>'
      }
    } catch (error) {
      console.error("Error loading guides:", error)
      container.innerHTML = '<p style="text-align: center; color: #cc0000;">Error loading guides.</p>'
    }
  }

  async function loadForms() {
    const container = document.getElementById("forms-container")
    if (!container) return

    container.innerHTML = '<tr><td colspan="3" style="text-align: center;">Loading forms...</td></tr>'

    try {
      const result = await apiCall("api/fetch_forms.php")

      if (result && result.success && result.data) {
        const forms = result.data

        // Check if user is admin
        const authData = sessionStorage.getItem("auth")
        const isAdmin = authData && JSON.parse(authData).role === "admin"

        const createBtn = document.getElementById("create-form-btn")
        if (createBtn) {
          createBtn.style.display = isAdmin ? "inline-block" : "none"
        }

        if (forms.length === 0) {
          container.innerHTML =
            '<tr><td colspan="3" style="text-align: center; color: #666;">No forms available at this time.</td></tr>'
        } else {
          container.innerHTML = forms
            .map((form) => {
              // Extract file extension from URL
              let fileType = "PDF"
              if (form.download_url) {
                const url = form.download_url.toLowerCase()
                if (url.includes(".docx") || url.includes(".doc")) fileType = "DOCX"
                else if (url.includes(".xlsx") || url.includes(".xls")) fileType = "XLSX"
              }

              return `
              <tr data-id="${form.content_id}">
                <td>${form.title || "Untitled Form"}</td>
                <td>${form.description || ""}</td>
                <td>
                  <a href="${form.download_url || "#"}" class="download-link" ${form.download_url ? 'target="_blank"' : ""}>
                    <i class="icon-download"></i> Download (${fileType})
                  </a>
                  ${
                    isAdmin
                      ? `
                    <div style="display: inline-flex; gap: 8px; margin-left: 15px;">
                      <button onclick="editForm(${form.content_id})" class="edit-btn" title="Edit" style="background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #666;">✎</button>
                      <button onclick="deleteForm(${form.content_id})" class="delete-btn" title="Delete" style="background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #cc0000;">🗑</button>
                    </div>
                  `
                      : ""
                  }
                </td>
              </tr>
            `
            })
            .join("")
        }
      } else {
        container.innerHTML =
          '<tr><td colspan="3" style="text-align: center; color: #cc0000;">Failed to load forms.</td></tr>'
      }
    } catch (error) {
      console.error("Error loading forms:", error)
      container.innerHTML =
        '<tr><td colspan="3" style="text-align: center; color: #cc0000;">Error loading forms.</td></tr>'
    }
  }

  window.toggleUploadMethod = (method) => {
    const urlGroup = document.getElementById("url-input-group")
    const fileGroup = document.getElementById("file-input-group")
    const urlInput = document.getElementById("edit-form-url")
    const fileInput = document.getElementById("edit-form-file")

    if (method === "url") {
      urlGroup.style.display = "block"
      fileGroup.style.display = "none"
      urlInput.required = true
      fileInput.required = false
    } else {
      urlGroup.style.display = "none"
      fileGroup.style.display = "block"
      urlInput.required = false
      fileInput.required = true
    }
  }

  window.createGuide = () => {
    const modal = document.getElementById("edit-guide-modal")
    if (!modal) return

    // Clear form
    document.getElementById("edit-guide-id").value = ""
    document.getElementById("edit-guide-title").value = ""
    document.getElementById("edit-guide-description").value = ""
    document.getElementById("edit-guide-url").value = ""

    // Update modal
    document.getElementById("guide-modal-title").textContent = "Create New Guide"
    document.getElementById("guide-submit-btn").textContent = "Create Guide"

    modal.style.display = "block"
  }

  window.editGuide = async (guideId) => {
    const modal = document.getElementById("edit-guide-modal")
    if (!modal) return

    try {
      const result = await apiCall("api/fetch_guides.php")

      if (result && result.success && result.data) {
        const guide = result.data.find((g) => g.content_id == guideId)

        if (guide) {
          document.getElementById("edit-guide-id").value = guide.content_id
          document.getElementById("edit-guide-title").value = guide.title || ""
          document.getElementById("edit-guide-description").value = guide.description || ""
          document.getElementById("edit-guide-url").value = guide.content_url || ""

          document.getElementById("guide-modal-title").textContent = "Edit Guide"
          document.getElementById("guide-submit-btn").textContent = "Update Guide"

          modal.style.display = "block"
        }
      }
    } catch (error) {
      console.error("Error loading guide:", error)
      alert("Failed to load guide data")
    }
  }

  window.closeEditGuideModal = () => {
    const modal = document.getElementById("edit-guide-modal")
    if (modal) modal.style.display = "none"
  }

  window.deleteGuide = (guideId) => {
    const modal = document.getElementById("confirm-delete-guide-modal")
    if (!modal) return

    document.getElementById("delete-guide-id").value = guideId
    modal.style.display = "block"
  }

  window.closeConfirmDeleteGuideModal = () => {
    const modal = document.getElementById("confirm-delete-guide-modal")
    if (modal) modal.style.display = "none"
  }

  window.confirmDeleteGuide = async () => {
    const guideId = document.getElementById("delete-guide-id").value

    try {
      const response = await fetch("api/delete_guide.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ content_id: guideId }),
      })

      const data = await response.json()

      if (data.success) {
        alert("Guide deleted successfully!")
        window.closeConfirmDeleteGuideModal()
        loadGuides()
      } else {
        alert("Error: " + data.message)
      }
    } catch (error) {
      console.error("Delete error:", error)
      alert("Network error. Failed to delete guide.")
    }
  }

  const editGuideForm = document.getElementById("edit-guide-form")
  if (editGuideForm) {
    editGuideForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const guideId = document.getElementById("edit-guide-id").value
      const title = document.getElementById("edit-guide-title").value.trim()
      const description = document.getElementById("edit-guide-description").value.trim()
      const url = document.getElementById("edit-guide-url").value.trim()

      if (!title || !description) {
        alert("Title and description are required")
        return
      }

      try {
        const response = await fetch("api/manage_guide.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            content_id: guideId === "" ? null : guideId,
            title: title,
            description: description,
            content_url: url,
          }),
        })

        const data = await response.json()

        if (data.success) {
          alert(guideId ? "Guide updated successfully" : "Guide created successfully")
          window.closeEditGuideModal()
          loadGuides()
        } else {
          alert("Error: " + data.message)
        }
      } catch (error) {
        console.error("Save error:", error)
        alert("Network error. Failed to save guide.")
      }
    })
  }

  window.createForm = () => {
    const modal = document.getElementById("edit-form-modal")
    if (!modal) return

    // Clear form
    document.getElementById("edit-form-id").value = ""
    document.getElementById("edit-form-title").value = ""
    document.getElementById("edit-form-description").value = ""
    document.getElementById("edit-form-url").value = ""
    document.getElementById("edit-form-file").value = ""

    document.querySelector('input[name="upload-method"][value="url"]').checked = true
    window.toggleUploadMethod("url")

    // Update modal
    document.getElementById("form-modal-title").textContent = "Add New Form"
    document.getElementById("form-submit-btn").textContent = "Create Form"

    modal.style.display = "block"
  }

  window.editForm = async (formId) => {
    const modal = document.getElementById("edit-form-modal")
    if (!modal) return

    try {
      const result = await apiCall("api/fetch_forms.php")

      if (result && result.success && result.data) {
        const form = result.data.find((f) => f.content_id == formId)

        if (form) {
          document.getElementById("edit-form-id").value = form.content_id
          document.getElementById("edit-form-title").value = form.title || ""
          document.getElementById("edit-form-description").value = form.description || ""
          document.getElementById("edit-form-url").value = form.download_url || ""
          document.getElementById("edit-form-file").value = ""

          document.querySelector('input[name="upload-method"][value="url"]').checked = true
          window.toggleUploadMethod("url")

          document.getElementById("form-modal-title").textContent = "Edit Form"
          document.getElementById("form-submit-btn").textContent = "Update Form"

          modal.style.display = "block"
        }
      }
    } catch (error) {
      console.error("Error loading form:", error)
      alert("Failed to load form data")
    }
  }

  window.closeEditFormModal = () => {
    const modal = document.getElementById("edit-form-modal")
    if (modal) modal.style.display = "none"
  }

  window.deleteForm = (formId) => {
    const modal = document.getElementById("confirm-delete-form-modal")
    if (!modal) return

    document.getElementById("delete-form-id").value = formId
    modal.style.display = "block"
  }

  window.closeConfirmDeleteFormModal = () => {
    const modal = document.getElementById("confirm-delete-form-modal")
    if (modal) modal.style.display = "none"
  }

  window.confirmDeleteForm = async () => {
    const formId = document.getElementById("delete-form-id").value

    try {
      const response = await fetch("api/delete_form.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ content_id: formId }),
      })

      const data = await response.json()

      if (data.success) {
        alert("Form deleted successfully!")
        window.closeConfirmDeleteFormModal()
        loadForms()
      } else {
        alert("Error: " + data.message)
      }
    } catch (error) {
      console.error("Delete error:", error)
      alert("Network error. Failed to delete form.")
    }
  }

  const editFormForm = document.getElementById("edit-form-form")
  if (editFormForm) {
    editFormForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const formId = document.getElementById("edit-form-id").value
      const title = document.getElementById("edit-form-title").value.trim()
      const description = document.getElementById("edit-form-description").value.trim()
      const uploadMethod = document.querySelector('input[name="upload-method"]:checked').value
      const downloadUrl = document.getElementById("edit-form-url").value.trim()
      const fileInput = document.getElementById("edit-form-file")

      if (!title || !description) {
        alert("Title and description are required")
        return
      }

      if (uploadMethod === "url" && !downloadUrl) {
        alert("Please provide a download URL")
        return
      }

      if (uploadMethod === "file" && !fileInput.files[0] && !formId) {
        alert("Please select a file to upload")
        return
      }

      let finalDownloadUrl = downloadUrl

      try {
        if (uploadMethod === "file" && fileInput.files[0]) {
          const uploadFormData = new FormData()
          uploadFormData.append("form_file", fileInput.files[0])

          const uploadResponse = await fetch("api/upload_form_document.php", {
            method: "POST",
            body: uploadFormData,
          })

          const uploadResult = await uploadResponse.json()

          if (!uploadResult.success) {
            alert("File upload failed: " + uploadResult.message)
            return
          }

          // Use the uploaded file path as the download URL
          finalDownloadUrl = uploadResult.file_path
        }

        const response = await fetch("api/manage_form.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            content_id: formId === "" ? null : formId,
            title: title,
            description: description,
            download_url: finalDownloadUrl,
          }),
        })

        const data = await response.json()

        if (data.success) {
          alert(formId ? "Form updated successfully" : "Form created successfully")
          window.closeEditFormModal()
          loadForms()
        } else {
          alert("Error: " + data.message)
        }
      } catch (error) {
        console.error("Form save error:", error)
        alert("Network error. Failed to save form.")
      }
    })
  }

  if (window.location.pathname.includes("guides.html")) {
    loadGuides()
    loadForms()
  }

  // Make load functions available globally
  window.loadGuides = loadGuides
  window.loadForms = loadForms

  // Initialize
  checkAuthenticationStatus()
})
