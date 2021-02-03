const compose = () => {
    document.body.appendChild(createBox());
}

const createBox = () => {
    const box = document.createElement("div");
    box.classList.add("c-compose");

    const title = document.createElement("h1");
    title.textContent = "Compose message";
    box.appendChild(title);

    const form = document.createElement("form");
    form.setAttribute("name", "compose_form");
    form.setAttribute("method", "post");

    const receiver = document.createElement("input");
    receiver.setAttribute("type", "email");
    receiver.setAttribute("id", "compose_form_receiver");
    receiver.setAttribute("name", "compose_form[receiver]");
    receiver.setAttribute("required", "required");
    receiver.setAttribute("placeholder", "Who are you sending this to?");
    receiver.classList.add("form-control");
    form.appendChild(receiver);

    const subject = document.createElement("input");
    subject.setAttribute("type", "text");
    subject.setAttribute("id", "compose_form_subject");
    subject.setAttribute("name", "compose_form[subject]");
    subject.setAttribute("placeholder", "Write a subject");
    subject.classList.add("form-control");
    form.appendChild(subject);

    const content = document.createElement("textarea");
    content.setAttribute("id", "compose_form_content");
    content.setAttribute("name", "compose_form[content]");
    content.setAttribute("placeholder", "Write your message!");
    content.classList.add("form-control");
    form.appendChild(content);

    const sendButton = document.createElement("button");
    sendButton.setAttribute("type", "submit");
    sendButton.classList.add("compose-button");
    sendButton.textContent = "Send message";
    form.appendChild(sendButton);

    box.appendChild(form);

    return box;
}

const composeButton = document.querySelector('[data-function="composeButton"]');
composeButton.addEventListener("click", compose);