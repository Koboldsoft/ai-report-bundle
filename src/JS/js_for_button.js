<script>
  // Get the select element
  const selectEl = document.getElementById('ctrl_Berichtstyp');

  // Find the parent widget
  const widget = document.querySelector('.widget.widget-select.prop-Berichtstyp.field.is-horizontal');

  if (widget && selectEl) {
    const selectDiv = widget.querySelector('.select.is-fullwidth');

    if (selectDiv) {
      // Create a flex wrapper
      const wrapper = document.createElement('div');
      wrapper.style.display = 'flex';
      wrapper.style.alignItems = 'center';
      wrapper.style.gap = '0.5rem';

      // Move the select into the wrapper
      selectDiv.parentNode.insertBefore(wrapper, selectDiv);
      wrapper.appendChild(selectDiv);

      // Create the button
      const button = document.createElement('button');
      button.textContent = 'KI Bericht erstellen';
      button.type = 'button';
      button.className = 'button is-primary'; // gray by default
      button.disabled = false; // disabled initially
      button.id = 'ajaxButton';

      // Append the button next to the select
      wrapper.appendChild(button);

      
    }
  }

  // Get URL query parameters
  const params = new URLSearchParams(window.location.search);

  // Read "parentid" from URL
  const parentId = params.get('parentid');

  document.addEventListener('DOMContentLoaded', () => {
    const selectEl = document.getElementById('ctrl_Berichtstyp');
    const button = document.getElementById('ajaxButton');

    if (!selectEl || !button) {
      console.warn('Select or button not found');
      return;
    }

    button.addEventListener('click', async () => {
      // Save original button content
      const originalHtml = button.innerHTML;

      // Disable button and show spinner
      button.disabled = true;
      button.innerHTML = `
        <span class="spinner" style="
          display: inline-block;
          width: 16px;
          height: 16px;
          border: 2px solid #fff;
          border-top: 2px solid transparent;
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
          vertical-align: middle;
          margin-right: 6px;
        "></span> erstelle Bericht ...`;

      try {
        // Call Symfony route with parentid parameter
        const response = await fetch(`/button/press?auftrag=${encodeURIComponent(parentId)}`);
        const text = await response.text();
        console.log(text);

        // ✅ If TinyMCE is active, update its content
        if (window.tinymce && tinymce.activeEditor) {
          tinymce.activeEditor.setContent(text);
        } else {
          // Fallback: update text content in existing element
          const body = document.getElementById('tinymce');
          if (body) {
            const p = body.querySelector('p');
            if (p) {
              p.textContent = text;
            } else {
              console.warn('No <p> element found inside #tinymce to update.');
            }
          } else {
            alert(text); // last resort fallback
          }
        }
      } catch (err) {
        console.error('AJAX error:', err);
        alert('An error occurred while contacting the server.');
      } finally {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalHtml;
      }
    });
  });

  // Add spinner animation globally
  const style = document.createElement('style');
  style.textContent = `
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  `;
  document.head.appendChild(style);
</script>