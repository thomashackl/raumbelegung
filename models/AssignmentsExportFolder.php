<?php
/**
 * AssignmentsExportFolder.class.php
 *
 * This folder type provides CSV export files for room assignments.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Luna
 */
class AssignmentsExportFolder implements FolderType
{
    protected $folder;

    /**
     * @param Folder|null folder The folder object for this FolderType
     */
    public function __construct($folder = null)
    {
        if ($folder instanceof AssignmentsExportFolder) {
            $this->folder = $folder->folder;
        } elseif ($folder instanceof Folder) {
            $this->folder = $folder;
        } else {
            $this->folder = Folder::build($folder);
        }
        $this->folder['folder_type'] = get_class($this);
    }

    /**
     * Retrieves or creates the top folder for export files.
     *
     * @param string $client_id The client-ID of the LunaClient whose top folder
     *     shall be returned
     *
     * @return AssignmentsExportFolder|null The top folder of the assignment export files.
     *                                      If the folder can't be retrieved, null is returned.
     */
    public static function findTopFolder($user_id)
    {
        //try to find the top folder:
        $folder = Folder::findOneBySQL("`parent_id` = '' AND `range_id` = 'roomplanplugin'");

        if (!$folder) {
            $folder = new Folder();
            $folder->user_id = $GLOBALS['user']->id;
            $folder->parent_id = '';
            $folder->range_id = 'roomplanplugin';
            $folder->range_type = 'roomplanplugin';
            $folder->name = 'Raumbelegungen';
        }

        $topFolder = new AssignmentsExportFolder($folder);
        $topFolder->store();
        return $topFolder;
    }

    /**
     * Creates a root folder (top folder) for a user referenced by its ID.
     *
     * @param string $range_id Range to add the folder to. In this case,
     *                         the range for the top folder is static.
     * @param string $range_type Range type, normally "course", "user" or "institute".
     *                           Here we have a static type.
     * @param string $folder_type A folder type.
     *
     * @return AssignmentsExportFolder A new AssignmentsExportFolder as root folder for all CSV files.
     */
    public static function createTopFolder($range_id, $range_type, $folder_type = 'RootFolder')
    {
        return new AssignmentsExportFolder(
            Folder::createTopFolder(
                'assignmentsexport',
                'roomplanplugin',
                'AssignmentsExportFolder'
            )
        );
    }

    /**
     * This method returns always false since AssignmentsExportFolder types are not
     * creatable in standard folders. They are a standalone folder type.
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    /**
     * Returns a localised name of the AssignmentsExportFolder type.
     */
    public static function getTypeName()
    {
        return dgettext('roomplanplugin', 'CSV-Exporte der Raumbelegungen');
    }

    /**
     * Finds or creates a subfolder for the given year and month.
     * @param $year
     * @param $month
     */
    public static function findSubfolder($year, $month) {
        $topFolder = self::findTopFolder('');

        // Find or create intermediate folder for given year.
        $f = Folder::findOneBySQL("`name` = :year AND `parent_id` = :parent",
            ['year' => $year, 'parent' => $topFolder->id]);
        if (!$f) {
            $f = new Folder();
            $f->user_id = $GLOBALS['user']->id;
            $f->parent_id = $topFolder->getId();
            $f->range_id = 'roomplanplugin';
            $f->range_type = 'roomplanplugin';
            $f->name = $year;
        }
        //check if that was successful:
        $yearFolder = new AssignmentsExportFolder($f);
        $yearFolder->store();

        // Find or create folder for given month.
        $f = Folder::findOneBySQL("`name` = :month AND `parent_id` = :parent",
            ['month' => $year, 'parent' => $yearFolder->id]);
        if (!$f) {
            $f = new Folder();
            $f->user_id = $GLOBALS['user']->id;
            $f->parent_id = $yearFolder->getId();
            $f->range_id = 'roomplanplugin';
            $f->range_type = 'roomplanplugin';
            $f->name = $month;
        }
        //check if that was successful:
        $monthFolder = new AssignmentsExportFolder($f);
        $monthFolder->store();
        return $monthFolder;
    }

    /**
     * Returns the Icon object for the AssignmentsExportFolder type.
     */
    public function getIcon($role)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
            ? 'folder-empty'
            : 'folder-full';
        return Icon::create($shape, $role);
    }

    /**
     * Returns the ID of the folder object of this AssignmentsExportFolder.
     */
    public function getId()
    {
        return $this->folder->id;
    }

    /**
     * See method AssignmentsExportFolder::isReadable.
     */
    public function isVisible($user_id)
    {
        return $this->isReadable($user_id);
    }

    /**
     * This method checks if a specified user can read the AssignmentsExportFolder object.
     *
     * @param string $user_id The ID of the user whose read permission
     *     shall be checked.
     *
     * @return True, if the user, specified by $user_id, can read the folder,
     *     false otherwise.
     */
    public function isReadable($user_id)
    {
        return ($GLOBALS['perm']->have_perm('root') ||
            $GLOBALS['user']->getAuthenticatedUser()->hasRole('Belegungsplan'));
    }

    /**
     * Writeable for all users that can use this plugin.
     */
    public function isWritable($user_id)
    {
        return false;
    }

    /**
     * AssignmentsExportFolders are never editable.
     */
    public function isEditable($user_id)
    {
        return false;
    }

    /**
     * AssignmentsExportFolders can never have subfolders as everything is generated automatically.
     */
    public function isSubfolderAllowed($user_id)
    {
        return false;
    }

    /**
     * AssignmentsExportFolders don't have a description template.
     */
    public function getDescriptionTemplate()
    {
        return '';
    }

    /**
     * @return FolderType[]
     */
    public function getSubfolders()
    {
        $subfolders = [];
        $database_subfolders = Folder::findByParent_id($this->getId(), "ORDER BY name");
        foreach ($database_subfolders as $subfolder) {
            //check FolderType of subfolder
            $subfolders[] = $subfolder->getTypedFolder();
        }
        return $subfolders;
    }

    /**
     * Returns the files of this AssignmentsExportFolder (e.g. the files attached).
     *
     * @return FileRef[] An array of FileRef objects containing all files
     *     that are placed inside this folder.
     */
    public function getFiles()
    {
        if ($this->folder) {
            return SimpleCollection::createFromArray($this->folder->file_refs->getArrayCopy())->orderBy('mkdate');
        }
        return [];
    }

    /**
     * Returns the parent-folder as a StandardFolder
     * @return FolderType
     */
    public function getParent()
    {
        return $this->folderdata->parentfolder
            ? $this->folderdata->parentfolder->getTypedFolder()
            : null;
    }

    /**
     * AssignmentsExportFolders don't have an edit template.
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * AssignmentsExportFolders don't have an edit template and therefore cannot
     * handle requests from such templates.
     */
    public function setDataFromEditTemplate($request)
    {
    }

    /**
     * No validation is needed as all files are generated automatically.
     *
     * @param array $uploaded_file The uploaded file that shall be validated.
     * @param string $user_id The user who wishes to upload a file
     *     in this MessageFolder.
     *
     * @return string|null An error message on failure, null on success.
     */
    public function validateUpload($uploaded_file, $user_id)
    {
    }

    /**
     * This method handles creating a file inside the AssignmentsExportFolder.
     *
     * @param File|array $file The file that shall be created inside
     *     the AssignmentsExportFolder.
     *
     * @return FileRef|null On success, a FileRef for the given file
     *     is returned. Null otherwise.
     */
    public function createFile($file)
    {
        if (!$this->folder) {
            return MessageBox::error(
                _('Datei kann nicht erstellt werden, da kein Ordner angegeben wurde, in dem diese erstellt werden kann!')
            );
        }

        $new_file = $file;
        $file_ref_data = [];

        if (!is_a($new_file, 'File')) {
            $new_file = new File();
            $new_file->name      = $file['name'];
            $new_file->mime_type = $file['type'];
            $new_file->size      = $file['size'];
            $new_file->storage   = 'disk';
            $new_file->id        = $new_file->getNewId();
            $new_file->connectWithDataFile($file['tmp_name']);
        }

        if ($new_file->isNew()) {
            $new_file->store();
        }

        $file_ref_data['name'] = $file['name'];
        $file_ref_data['description'] = '';

        $default_license = ContentTermsOfUse::find(
            'SELFMADE_NONPUB'
        );
        $file_ref_data['content_terms_of_use_id'] = $default_license->id;

        return $this->folder->linkFile(
            $new_file,
            array_filter($file_ref_data)
        );
    }

    /**
     * Handles the deletion of a file inside this folder.
     *
     * @param string $file_ref_id The ID of the FileRef whose file
     *     shall be deleted.
     *
     * @return True, if the file has been deleted successfully, false otherwise.
     */
    public function deleteFile($file_ref_id)
    {
        $file_ref = $this->folderdata->file_refs->find($file_ref_id);

        if ($file_ref) {
            return $file_ref->delete();
        }
    }

    /**
     * Stores the AssignmentsExportFolder object.
     *
     * @return True, if the AssignmentsExportFolder has been stored successfully,
     *     false otherwise.
     */
    public function store()
    {
        return $this->folder->store();
    }

    /**
     * @param FolderType $foldertype
     * @return bool
     */
    public function createSubfolder(FolderType $folderdata)
    {
    }

    /**
     * @param string $subfolder_id
     * @return bool
     */
    public function deleteSubfolder($subfolder_id)
    {
    }

    /**
     * Deletes the AssignmentsExportFolder object.
     *
     * @return True, if the AssignmentsExportFolder has been deleted successfully,
     *     false otheriwse.
     */
    public function delete()
    {
        return $this->folder->delete();
    }

    /**
     * See method AssignmentsExportFolder::isReadable
     */
    public function isFileDownloadable($file_ref_id, $user_id)
    {
        return $this->isReadable($user_id);
    }

    /**
     * Files inside AssignmentsExportFolders are editable.
     */
    public function isFileEditable($file_ref_id, $user_id)
    {
        return true;
    }

    /**
     * Files inside AssignmentsExportFolders are writable.
     */
    public function isFileWritable($file_ref_id, $user_id)
    {
        return true;
    }
}
