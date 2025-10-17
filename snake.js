var canvas = document.getElementById('game');
var context = canvas.getContext('2d');

var grid = 16;
var count = 0;
var score = 0; // now dynamic

var snake = {
  x: 160,
  y: 160,
  dx: grid,
  dy: 0,
  cells: [],
  maxCells: 4
};

var apple = {
  x: 320,
  y: 320
};

function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min)) + min;
}

function loop() {
  requestAnimationFrame(loop);

  if (++count < 4) return;
  count = 0;

  context.clearRect(0, 0, canvas.width, canvas.height);

  // draw score first
  context.fillStyle = 'white';
  context.font = '16px Arial';
  context.fillText(`Score: ${score}`, 10, 20);

  // move snake
  snake.x += snake.dx;
  snake.y += snake.dy;

  // wrap snake on edges
  if (snake.x < 0) snake.x = canvas.width - grid;
  else if (snake.x >= canvas.width) snake.x = 0;
  if (snake.y < 0) snake.y = canvas.height - grid;
  else if (snake.y >= canvas.height) snake.y = 0;

  snake.cells.unshift({x: snake.x, y: snake.y});
  if (snake.cells.length > snake.maxCells) snake.cells.pop();

  // draw apple
  context.fillStyle = 'red';
  context.fillRect(apple.x, apple.y, grid-1, grid-1);

  // draw snake & handle collisions
  context.fillStyle = 'green';
  snake.cells.forEach(function(cell, index) {
    context.fillRect(cell.x, cell.y, grid-1, grid-1);

    if (cell.x === apple.x && cell.y === apple.y) {
      snake.maxCells++;
      score++; // increment score when apple eaten
      apple.x = getRandomInt(0, 25) * grid;
      apple.y = getRandomInt(0, 25) * grid;
    }

    for (var i = index + 1; i < snake.cells.length; i++) {
      if (cell.x === snake.cells[i].x && cell.y === snake.cells[i].y) {
        // reset snake
        snake.x = 160;
        snake.y = 160;
        snake.cells = [];
        snake.maxCells = 4;
        snake.dx = grid;
        snake.dy = 0;
        score = 0; // reset score
        apple.x = getRandomInt(0, 25) * grid;
        apple.y = getRandomInt(0, 25) * grid;
      }
    }
  });
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'ArrowLeft' && snake.dx === 0) {
    snake.dx = -grid; snake.dy = 0;
  } else if (e.key === 'ArrowUp' && snake.dy === 0) {
    snake.dx = 0; snake.dy = -grid;
  } else if (e.key === 'ArrowRight' && snake.dx === 0) {
    snake.dx = grid; snake.dy = 0;
  } else if (e.key === 'ArrowDown' && snake.dy === 0) {
    snake.dx = 0; snake.dy = grid;
  }
});

requestAnimationFrame(loop);
