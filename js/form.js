const handlerPath = "/TESTING/testHandler.php";

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("[js-form-typical]");
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const data = new FormData(this);
    void sendRequest({
      url: handlerPath,
      responseType: "json",
      method: "post",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: data,
      callback: function (r) {
        if (r.success) {
          // const modal = document.querySelector('[js-form-modal]')
          // modal.classList.add('active')
          form.reset();
        } else {
          alert(r.message);
          // const modalError = document.querySelector('[js-form-errors]')
          // modalError.classList.add('active')
        }
      },
    });
  });
});

async function sendRequest(params) {
  const response = await fetch(params.url, params);
  let r = "";

  if (response.ok) {
    switch (params.responseType) {
      case "text":
        r = await response.text();
        break;
      case "json":
        r = await response.json();
        break;
      default:
        r = new DOMParser().parseFromString(await response.text(), "text/html");
    }

    params.callback(r);
    return true;
  } else {
    alert(`Произошла ошибка ${response.status}`);
    return false;
  }
}
