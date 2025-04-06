<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    $app->get('/tasks', function (Request $request, Response $response) {
        $sql = $this->get('db')->prepare("SELECT * FROM tasks");
        $sql->execute();
        $tasks = $sql->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($tasks));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // GET a single task
    $app->get('/tasks/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $sql = $this->get('db')->prepare("SELECT * FROM tasks WHERE id = :id");
        $sql->bindParam(':id', $id);
        $sql->execute();
        $task = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            $response->getBody()->write(json_encode(['error' => 'Task not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($task));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // POST - Create a new task
    $app->post('/tasks', function (Request $request, Response $response) {
        $data = $request->getParsedBody();

        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $completed = isset($data['completed']) ? (bool) $data['completed'] : false;

        $sql = $this->get('db')->prepare(
            "INSERT INTO tasks (title, description, completed) VALUES (:title, :description, :completed) RETURNING id"
        );

        $sql->bindParam(':title', $title);
        $sql->bindParam(':description', $description);
        $sql->bindParam(':completed', $completed, PDO::PARAM_BOOL);

        $sql->execute();
        $newId = $sql->fetchColumn();

        $response->getBody()->write(json_encode([
            'id' => $newId,
            'title' => $title,
            'description' => $description,
            'completed' => $completed,
            'created_at' => date('c')
        ]));

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    });

    // PUT - Update a task
    $app->put('/tasks/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();

        // Check if task exists
        $check = $this->get('db')->prepare("SELECT 1 FROM tasks WHERE id = :id");
        $check->bindParam(':id', $id);
        $check->execute();

        if (!$check->fetchColumn()) {
            $response->getBody()->write(json_encode(['error' => 'Task not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Update task
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $completed = isset($data['completed']) ? (bool) $data['completed'] : false;

        $sql = $this->get('db')->prepare(
            "UPDATE tasks SET title = :title, description = :description, completed = :completed WHERE id = :id"
        );

        $sql->bindParam(':id', $id);
        $sql->bindParam(':title', $title);
        $sql->bindParam(':description', $description);
        $sql->bindParam(':completed', $completed, PDO::PARAM_BOOL);

        $sql->execute();

        $response->getBody()->write(json_encode([
            'id' => (int) $id,
            'title' => $title,
            'description' => $description,
            'completed' => $completed
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    // DELETE a task
    $app->delete('/tasks/{id}', function (Request $request, Response $response, $args) {
        $id = $args['id'];

        $sql = $this->get('db')->prepare("DELETE FROM tasks WHERE id = :id");
        $sql->bindParam(':id', $id);
        $sql->execute();

        if ($sql->rowCount() === 0) {
            $response->getBody()->write(json_encode(['error' => 'Task not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        return $response->withStatus(204);
    });
};