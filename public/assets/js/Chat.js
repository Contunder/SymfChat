let Chat = (function () {

    let User;
    let Conversation;
    let UserName;
    let UserLastname;
    let Route = "https://127.0.0.1:8000/";

    // PARAMETRE DU SCRIPT
    function init(ParamUser, ParamConversation, ParamUserName, ParamUserLastname) {
        User = ParamUser;
        Conversation = ParamConversation;
        UserName = ParamUserName;
        UserLastname = ParamUserLastname;
    }

    // FONCTION POUR AUTOSCOLL A L'AJOUT D'UN MESSAGE
    const scrollDown = function () {
        let chatBox = document.getElementById('chatBox');
        chatBox.scrollTop = chatBox.scrollHeight;
    }
    scrollDown();

    // MESSAGE D'UN UTILISATEUR
    // RENVOIE LA REPONSE AU WEBSOCKET AINSI QU'A L'UTILISATEUR ACTUELLE
    const sendMessage = function () {
        const message = {
            UserId: User,
            ConversationId: Conversation,
            UserName: UserName,
            UserLastname: UserLastname,
            Message: document.getElementById("Message").value
        };
        socket.send(JSON.stringify(message));
        addMessage(message.UserId, message.ConversationId, message.Message, message.UserName, message.UserLastname);
        scrollDown();
    }

    // MESSAGE QUAND UN UTILISATEUR ECRIT UN MESSAGE
    // RENVOIE LA REPONSE AU WEBSOCKET
    const writeMessage = function () {
        const message = {
            UserId: User,
            ConversationId: Conversation,
            UserName: UserName,
            UserLastname: UserLastname,
            Message: "..."
        };
        socket.send(JSON.stringify(message));
    }

    // MESSAGE QUAND UN UTILISATEUR ANNULE SONT MESSAGE
    // RENVOIE LA REPONSE AU WEBSOCKET AINSI QU'A L'UTILISATEUR ACTUELLE
    const deleteMessage = function () {
        const message = {
                UserId: User,
                ConversationId: Conversation,
                UserName: UserName,
                UserLastname: UserLastname,
                Message: "delete"
            }
        ;
        socket.send(JSON.stringify(message));
    }

    // CONNEXION AU WEBSOCKET A L'OUVERTURE DE LA PAGE
    const socket = new WebSocket("ws://127.0.0.1:3001");
    socket.addEventListener('open', function () {
        console.log("CONNECTED");
    });

    // TRIE POUR AJOUTER LES MESSAGES
    // AUTRE QUE L'UTILISATEUR ACTUELLE (UTILISATEUR QUI ECRIT, UTILISATEUR QUI EFFACE)
    // UTILISATEUR ACTUELLE (FICHIER IMAGE, FICHIER, MESSAGE)
    // AUTRE QUE L'UTILISATEUR ACTUELLE (FICHIER IMAGE, FICHIER, MESSAGE)
    function addMessage(UserId, ConversationId, Message, UserName, UserLastname) {
        if (ConversationId === Conversation) {
            const endMessage = "<small class='d-block'><b>" + UserName + " " + UserLastname + "</b></small></p>";
            if (Message === '...' && UserId !== User) {
                const messageHTML = "<small class='text-lg-end mt-0 d-block'><b>"
                    + UserName + " " + UserLastname +
                    " est en train d'écrire...</b></small>";
                document.getElementById("ChatInfo").innerHTML = messageHTML;
            } else if (Message === 'delete' || Message === '...') {
                document.getElementById("ChatInfo").innerHTML = "";
            } else {
                document.getElementById("ChatInfo").innerHTML = "";
                if (UserId === User) {
                    if (Message.substring(0, 5) === 'file:') {
                        if (Message.substring(Message.lastIndexOf('.') + 1) === 'jpg' || Message.substring(Message.lastIndexOf('.') + 1) === 'png' || Message.substring(Message.lastIndexOf('.') + 1) === 'jpeg') {
                            const messageHTML = "<p class='rtext align-self-end border rounded p-2 mb-1'>" +
                                "<img src='" + Route + Message.substring(5) + "' class='rtext' />" + endMessage;
                                document.getElementById("chatBox").innerHTML += messageHTML;
                            //javascript:window.location.reload();
                        } else {
                            const messageHTML = "<p class='rtext align-self-end border rounded p-2 mb-1'>" +
                                "<a href='" + Route + Message.substring(5) + "' download='' class='a'>Télécharger <i class='fas fa-download'></i></a>" + endMessage;
                            document.getElementById("chatBox").innerHTML += messageHTML;
                            //javascript:window.location.reload();
                        }
                    } else {
                        const messageHTML = "<p class='rtext align-self-end border rounded p-2 mb-1'>" + Message + endMessage;
                        document.getElementById("chatBox").innerHTML += messageHTML
                    }
                } else {
                    if (Message.substring(0, 5) === 'file:') {
                        if (Message.substring(Message.lastIndexOf('.') + 1) === 'jpg' || Message.substring(Message.lastIndexOf('.') + 1) === 'png' || Message.substring(Message.lastIndexOf('.') + 1) === 'jpeg') {
                            const messageHTML = "<p class='ltext align-self-start border rounded p-2 mb-1'>" +
                                "<img src='" + Route + Message.substring(5) + "' class='ltext' />" + endMessage;
                            document.getElementById("chatBox").innerHTML += messageHTML;
                            //javascript:window.location.reload();
                        } else {
                            const messageHTML = "<p class='ltext align-self-start border rounded p-2 mb-1'>" +
                                "<a href='" + Route + Message.substring(5) + "' download='' class='a'>Télécharger <i class='fas fa-download'></i></a>" + endMessage;
                            document.getElementById("chatBox").innerHTML += messageHTML;
                            //javascript:window.location.reload();
                        }
                    } else {
                        const messageHTML = "<p class='ltext align-self-start border rounded p-2 mb-1'>" + Message + endMessage;
                        document.getElementById("chatBox").innerHTML += messageHTML
                    }
                }
                scrollDown();
            }
        }

    }

    // ECOUTE LE WEBSOCKET POUR TRAITER LES MESSAGES RECU PAR UN AUTRE UTILISATEUR SEULEMENT
    socket.addEventListener("message", function (e) {
        try {
            const message = JSON.parse(e.data);
            addMessage(message.UserId, message.ConversationId, message.Message, message.UserName, message.UserLastname);
            scrollDown();
        } catch (e) {
            // Catch any errors
        }
    });

    // ECOUTE LE BOUTON POUR TRAITER LES MESSAGE
    document.getElementById("sendBtn").addEventListener("click", function () {
        sendMessage();
        document.getElementById("Message").value = ""
    });

    // ECOUTE LA TEXTAREA POUR TRAITER LES MESSAGE
    document.getElementById("Message").addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            sendMessage();
            document.getElementById("Message").value = ""
        } else if (event.key === "Backspace") {
            deleteMessage();
        } else {
            writeMessage();
        }

    });

    // ECOUTE LES CHANGEMENTS DE L'INPUT FILE OU DE LA DROP ZONE
    document.getElementById("form_upload_add_file").addEventListener("change", function () {
        const files_data = $('#form_upload_add_file').prop('files');
        handleFiles(files_data)
    }, false)
    window.addEventListener("drop", handleDrop, false);

    // RECUPERE LES FICHIERS DANS LA DROP ZONE
    function handleDrop(e) {
        e.preventDefault()
        let dt = e.dataTransfer
        let files = dt.files
        handleFiles(files)
    }

    // TRAITE SI IL Y A PLUSIEURS FICHIERS
    function handleFiles(files) {
        ([...files]).forEach(sendFile)
    }

    // ENVOIE LES FICHIER A PHP ET RENVOIE LA REPONSE AU WEBSOCKET AINSI QU'A L'UTILISATEUR ACTUELLE
    function sendFile(file) {
        const form_data = $('form[name="form_upload_add"]');
        let url = form_data.attr('action');
        let form = $('form')[0];
        let data = new FormData(form);
        data.append('file', file);
        $.ajax({
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                const messageHTML = "<div class='progress' style='width:200px'><div class='progress-bar' role='progressbar' style='width: 0%' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div></div>";
                document.getElementById("ChatInfo").innerHTML = messageHTML;
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        let percentComplete = ((evt.loaded / evt.total) * 100);
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete+'%');
                    }
                }, false);
                return xhr;
            },
            url: url,
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function(){
                $(".progress-bar").width('0%');
            },
            error:function(){
                document.getElementById("ChatInfo").innerHTML ='';
                alert('Problème lors du transfert :\nFichier accepté : Documents Texte, Images, Archives\nMax : 50Mo');
            },
            success: function (php_script_response) {
                if (php_script_response.fileSuccess === true) {
                    document.getElementById("ChatInfo").innerHTML ='';
                    const message = {
                        messUserId: User,
                        messConversationId: Conversation,
                        messUserName: UserName,
                        messUserLastname: UserLastname,
                        message: php_script_response.message,
                        messSize: php_script_response.size,
                        messFilename: php_script_response.filename,
                    };
                    console.log(php_script_response);
                    socket.send(JSON.stringify(message));
                    addMessage(message.messUserId, message.messConversationId, message.message, message.messUserName, message.messUserLastname, message.messFilename, message.messSize);
                } else {
                    alert(php_script_response.message);
                }

            }
        });
    }

    return {
        init: init
    }

}());