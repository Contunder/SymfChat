let Vocal = (function () {

    let User;
    let Conversation;
    let UserName;
    let UserLastname;
    let body = $('body');

    function init(ParamUser, ParamConversation, ParamUserName, ParamUserLastname) {
        User = ParamUser;
        Conversation = ParamConversation;
        UserName = ParamUserName;
        UserLastname = ParamUserLastname;
    }

    let startRecordingButtonPrime;
    let stopRecordingButtonPrime;
    let playButtonPrime;
    let sendButtonPrime;

    function clone() {
        startRecordingButtonPrime = document.getElementById('startRecordingButton').cloneNode(true);
        stopRecordingButtonPrime = document.getElementById('stopRecordingButton').cloneNode(true);
        playButtonPrime = document.getElementById('playButton').cloneNode(true);
        sendButtonPrime = document.getElementById('downloadButton').cloneNode(true);
    }

    function vocal() {

        let leftchannel = [];
        let rightchannel = [];
        let recorder = null;
        let recordingLength = 0;
        let volume = null;
        let mediaStream = null;
        let sampleRate = 44100;
        let context = null;
        let blob = null;

        body.on('click', '.start', function () {
            blob = null;
            leftchannel = [];
            rightchannel = [];
            // Initialize recorder
            navigator.getUserMedia(
                {
                    audio: true
                },
                function (e) {
                    console.log("user consent");

                    // creates the audio context
                    context = new AudioContext();

                    // creates an audio node from the microphone incoming stream
                    mediaStream = context.createMediaStreamSource(e);

                    // https://developer.mozilla.org/en-US/docs/Web/API/AudioContext/createScriptProcessor
                    // bufferSize: the onaudioprocess event is called when the buffer is full
                    let bufferSize = 2048;
                    let numberOfInputChannels = 2;
                    let numberOfOutputChannels = 2;
                    if (context.createScriptProcessor) {
                        recorder = context.createScriptProcessor(bufferSize, numberOfInputChannels, numberOfOutputChannels);
                    } else {
                        recorder = context.createJavaScriptNode(bufferSize, numberOfInputChannels, numberOfOutputChannels);
                    }

                    recorder.onaudioprocess = function (e) {
                        leftchannel.push(new Float32Array(e.inputBuffer.getChannelData(0)));
                        rightchannel.push(new Float32Array(e.inputBuffer.getChannelData(1)));
                        recordingLength += bufferSize;
                    }

                    // we connect the recorder
                    mediaStream.connect(recorder);
                    recorder.connect(context.destination);

                    document.getElementById("vocal").innerHTML = "";
                    document.getElementById("vocal").appendChild(stopRecordingButtonPrime);

                },
                function (e) {
                    console.error(e);
                });
        });

        body.on('click', '.stop', function () {

            // stop recording
            recorder.disconnect(context.destination);
            mediaStream.disconnect(recorder);

            // we flat the left and right channels down
            // Float32Array[] => Float32Array
            let leftBuffer = flattenArray(leftchannel, recordingLength);
            let rightBuffer = flattenArray(rightchannel, recordingLength);
            // we interleave both channels together
            // [left[0],right[0],left[1],right[1],...]
            let interleaved = interleave(leftBuffer, rightBuffer);

            // we create our wav file
            let buffer = new ArrayBuffer(44 + interleaved.length * 2);
            let view = new DataView(buffer);

            // RIFF chunk descriptor
            writeUTFBytes(view, 0, 'RIFF');
            view.setUint32(4, 44 + interleaved.length * 2, true);
            writeUTFBytes(view, 8, 'WAVE');
            // FMT sub-chunk
            writeUTFBytes(view, 12, 'fmt ');
            view.setUint32(16, 16, true); // chunkSize
            view.setUint16(20, 1, true); // wFormatTag
            view.setUint16(22, 2, true); // wChannels: stereo (2 channels)
            view.setUint32(24, sampleRate, true); // dwSamplesPerSec
            view.setUint32(28, sampleRate * 4, true); // dwAvgBytesPerSec
            view.setUint16(32, 4, true); // wBlockAlign
            view.setUint16(34, 16, true); // wBitsPerSample
            // data sub-chunk
            writeUTFBytes(view, 36, 'data');
            view.setUint32(40, interleaved.length * 2, true);

            // write the PCM samples
            let index = 44;
            let volume = 1;
            for (var i = 0; i < interleaved.length; i++) {
                view.setInt16(index, interleaved[i] * (0x7FFF * volume), true);
                index += 2;
            }

            // our final blob
            blob = new Blob([view], {type: 'audio/wav'});

            document.getElementById("vocal").innerHTML = "";
            document.getElementById("vocal").appendChild(startRecordingButtonPrime);
            document.getElementById("vocal").appendChild(playButtonPrime);
            document.getElementById("vocal").appendChild(sendButtonPrime);
        });

        body.on('click', '.play', function () {
            if (blob == null) {
                return;
            }

            var url = window.URL.createObjectURL(blob);
            var audio = new Audio(url);
            audio.play();
        });

        body.on('click', '.send', function () {
            if (blob == null) {
                return;
            }

            let url = URL.createObjectURL(blob);

            let a = document.createElement("a");
            document.body.appendChild(a);
            a.style = "display: none";
            a.href = url;
            a.download = "sample.wav";
            let file = new File([blob],getRandomString(20));
            Chat.sendFile(file);
        })
    }

    function getRandomString(length) {
        let randomChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for ( var i = 0; i < length; i++ ) {
            result += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
        }
        return result;
    }

    function flattenArray(channelBuffer, recordingLength) {
        var result = new Float32Array(recordingLength);
        var offset = 0;
        for (var i = 0; i < channelBuffer.length; i++) {
            var buffer = channelBuffer[i];
            result.set(buffer, offset);
            offset += buffer.length;
        }
        return result;
    }

    function interleave(leftChannel, rightChannel) {
        var length = leftChannel.length + rightChannel.length;
        var result = new Float32Array(length);

        var inputIndex = 0;

        for (var index = 0; index < length;) {
            result[index++] = leftChannel[inputIndex];
            result[index++] = rightChannel[inputIndex];
            inputIndex++;
        }
        return result;
    }

    function writeUTFBytes(view, offset, string) {
        for (var i = 0; i < string.length; i++) {
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    }

    return {
        vocal: vocal,
        clone: clone,
        init: init,
    }

}());