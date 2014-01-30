var app = require('http').createServer(handler)
  , io = require('socket.io').listen(app)
  , fs = require('fs')

app.listen(31070);

function handler (req, res) {
  res.writeHead(200);
  res.end("success");
}

io.sockets.on('connection', function (socket) {
 console.log("Established connection with a client.");
 socket.on('update_download_status', function(data){
    io.sockets.emit('download_status', data);
    console.log("Sending Download Status.");
    // console.log(data);
    // socket.emit(); // This one only
    // io.sockets.emit(); // All sockets
  });
  socket.on('update_live_debug', function(data){
    io.sockets.emit('live_debug', data);
    console.log("Sending Live Debug msg");
  });
});

