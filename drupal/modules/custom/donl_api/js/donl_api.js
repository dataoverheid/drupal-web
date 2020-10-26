window.onload = function() {

  // Build a system.
  window.ui = SwaggerUIBundle({
    url: drupalSettings.donl_api.url,
    dom_id: '#swagger-ui',
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset.slice(1)
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });

};
