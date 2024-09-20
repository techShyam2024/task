<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP - Simple To Do List App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>PHP - Simple To Do List App</h3>
        </div>
        <div class="card-body">
            <div id="errorContainer"></div>
            <!-- Add Task Form -->
            <form id="taskForm" class="mb-4">
                <div class="input-group">
                    <input type="text" id="taskInput" name="task" class="form-control" placeholder="Enter a new task" required>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>

            <!-- Task Table -->
            <table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Task</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="taskList">
        @foreach ($tasks as $task)
            <tr data-id="{{ $task->id }}">
                <td>{{ $task->id }}</td>
                <td>{{ $task->task }}</td>
                <td>
                <span class="badge
                    {{ $task->is_deleted ? 'bg-danger' : ($task->is_completed ? 'bg-secondary' : 'bg-success') }}">
                    {{ $task->is_deleted ? 'Deleted' : ($task->is_completed ? 'Done' : 'Pending') }}
                </span>
            </td>
            <td>
                @if (!$task->is_deleted && !$task->is_completed)
                    <button class="btn btn-success btn-sm completeTask">✔</button>
                @endif

                @if (!$task->is_deleted)
                    <button class="btn btn-danger btn-sm deleteTask">✖</button>
                @endif
            </td>
            </tr>
        @endforeach
    </tbody>
</table>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
   let errorContainer = $('#errorContainer');
    // Add a new task
    $('#taskForm').on('submit', function (e) {
        e.preventDefault();
        let task = $('#taskInput').val();
        
        $.ajax({
            type: "POST",
            url: "/tasks",
            data: { task: task, _token: '{{ csrf_token() }}' },
            success: function (response) {
                errorContainer.html('');
                $('#taskList').append(`
                    <tr data-id="${response.task.id}">
                        <td>${response.task.id}</td>
                        <td>${response.task.task}</td>
                        <td><span class="badge bg-success">Pending</span></td>
                        <td>
                            <button class="btn btn-success btn-sm completeTask">✔</button>
                            <button class="btn btn-danger btn-sm deleteTask">✖</button>
                        </td>
                    </tr>
                `);
                $('#taskInput').val(''); // Clear input after adding
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    errorContainer.html(`<div class="alert alert-danger">${errors.task[0]}</div>`);
                }
                
            }
        });
    });

    // Handle completing a task
    $('#taskList').on('click', '.completeTask', function () {
        let row = $(this).closest('tr');
        let taskId = row.data('id');

        $.ajax({
            type: "PUT",
            url: `/tasks/${taskId}/complete`,
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                let task = response.task;
                let statusBadge = row.find('td:nth-child(3) .badge');
                if (task.is_completed) {
                    statusBadge.removeClass('bg-success').addClass('bg-secondary').text('Done');
                    row.find('.completeTask').remove();  // Hide the complete button
                } else {
                    statusBadge.removeClass('bg-secondary').addClass('bg-success').text('Pending');
                    row.find('td:nth-child(4)').append(`<button class="btn btn-success btn-sm completeTask">✔</button>`); // Show the button again if undone
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to update task status:', xhr.responseText);
            }
        });
    });

    // Handle deleting a task (marking as deleted)
    $('#taskList').on('click', '.deleteTask', function () {
        let row = $(this).closest('tr');
        let taskId = row.data('id');

        if (confirm('Are you sure you want to delete this task?')) {
            $.ajax({
                type: "DELETE",
                url: `/tasks/${taskId}`,
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    let task = response.task;
                    let statusBadge = row.find('td:nth-child(3) .badge');
                    statusBadge.removeClass('bg-success bg-secondary').addClass('bg-danger').text('Deleted');
                    row.find('.completeTask').remove(); // Hide the complete button
                    row.find('.deleteTask').remove();   // Hide the delete button
                },
                error: function(xhr, status, error) {
                    console.error('Failed to delete task:', xhr.responseText);
                }
            });
        }
    });
});

</script>
</body>
</html>
