<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Note;

/**
 * NotesController is a Controller class from a Laravel project.
 * 
 * @author Ryan Janke
 */
class NotesController extends Controller
{
    /**
     * Force authentication on whole NotesController.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all User's Notes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get current user.
        $user = auth()->user();
        
        // Get most recently edited Note for User.
        // notes() is defined in User class.
        $notes = $user::findOrFail($user->id)->notes()->latest('updated_at')->get();

        // Get all Categories user has.
        $categories = $user::findOrFail($user->id)->categories()->orderBy('name')->get();

        // Return data to view.
        return view('notes.index', [
            'user' => $user,
            'notes' => $notes,
            'categories' => $categories
        ]);
    }

    /**
     * Show form for creating a new Note.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get current user.
        $user = auth()->user();

        // Get all Categories user has.
        $categories = $user::findOrFail($user->id)->categories()->orderBy('name')->get();

        // Return data to view.
        return view('notes.create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created Note in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate form request data.
        request()->validate([
            'title' => 'required',
            'body'=> 'required',
            'category_id' => 'required'
        ]);

        // Create new Note, assign fields, and Save.
        $note = new Note();

        $note->title = request('title');
        $note->body = request('body');
        $note->category_id = request('category_id');
        $note->user_id = auth()->user()->id;

        $note->save();

        // Return redirect with message.
        return redirect(route('notes.edit', $note->id))->with('status', 'Successfully saved note: ' . $note->title);
    }

    /**
     * Display specific Note.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
        // Return data to view.
        return view('notes.show', [
            'note' => $note
        ]);
    }

    /**
     * Show the form for editing specific Note.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Note $note)
    {
        // Return data to view.
        return view('notes.edit', [
            'note' => $note
        ]);
    }

    /**
     * Update the Note in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
        // Validate form request data.
        request()->validate([
            'title' => 'required',
            'body'=> 'required',
            'category_id' => 'required'
        ]);

        // Update fields and save.
        $note->title = request('title');
        $note->body = request('body');
        $note->category_id = request('category_id');

        $note->save();

        // Return redirect with message.
        return redirect($note->path())->with('status', 'Successfully saved note: ' . $note->title);
    }

    /**
     * Remove the Note from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        // Delete Note.
        $note->delete();

        // Return redirect with message.
        return redirect('dashboard')->with('status', 'Successfully deleted note: ' . $note->title);
    }
}