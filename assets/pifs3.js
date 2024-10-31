(function () {
	document.addEventListener("DOMContentLoaded", function () {
		var uploader = document.getElementById("pifs3-uploader");
		if (!uploader) {
			return;
		}

		uploader.addEventListener("change", async function (e) {
			e.preventDefault();

			// Prepare FormData
			var formData = new FormData();
			formData.append("action", "pisf3_file_upload");
			formData.append("_ajax_nonce", pisf3.nonce);
			formData.append("file", uploader.files[0]);            
			try {
				const response = await fetch(pisf3.ajax_url, {
					method: "POST",
					body: formData,
					credentials: "same-origin",
				});

				if (!response.ok) {
					throw new Error("Network response was not ok.");
				}

				const uploadStatus = await response.json();

				console.log(uploadStatus);
			} catch (error) {
				console.error("Error:", error);
			}
		});
	});
})();
