<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TasksResource;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TasksResource::collection(
            Task::where('user_id', Auth::user()->id)->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $request->validated($request->all());
        $task = Task::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        return new TasksResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
       $isNotAuthorized = $this->isNotAuthorized($task);

       return $isNotAuthorized ? $isNotAuthorized : new TasksResource($task);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if(Auth::user()->id !== $task->user_id){

            return $this->error('', 'You are not authorized to make this request', 403);
        }
        $task->update($request->all());

        return new TasksResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $isNotAuthorized = $this->isNotAuthorized($task);

       if($isNotAuthorized){

        return $isNotAuthorized;

       }else{

        $task->delete();

        return $this->success('', 'Task has been deleted successfully', '200');

       }
        
    }

    private function isNotAuthorized($task){

        if(Auth::user()->id !== $task->user_id)
        {
         return $this->error('', 'You are not authorized to make this request', 403);
        }

    }
}
