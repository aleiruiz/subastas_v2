<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\LanguageRequest;
use App\Repositories\Core\Interfaces\LanguageInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;
use App\Services\LanguageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    /**
     * @var LanguageService
     */
    public $languageService;
    public $languageRepository;

    public function __construct(LanguageService $languageService, LanguageInterface $languageRepository)
    {
        $this->languageService = $languageService;
        $this->languageRepository = $languageRepository;
    }

    public function index()
    {
        $searchFields = [
            ['name', __('Name')],
            ['short_code', __('Short Code')],
        ];

        $orderFields = [
            ['id', __("Serial")],
            ['name', __('Name')],
            ['short_code', __('Short Code')],
        ];

        $query = $this->languageRepository->paginateWithFilters($searchFields, $orderFields);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields);
        $data['title'] = __('Languages');
        return view('backend.languages.index', $data);
    }

    public function create()
    {
        $data['title'] = __('Create New Language');
        return view('backend.languages.create', $data);
    }

    public function store(LanguageRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->only(['name', 'short_code']);

            if ($request->hasFile('icon')) {
                $filePath = config('commonconfig.language_icon');
                $fileName = $params['short_code'];
                $params['icon'] = app(FileUploadService::class)->upload($request->file('icon'), $filePath, $fileName, $prefix = '', $suffix = '', $disk = 'public', $width = 120, $height = 80);
            }

            $language = app(LanguageInterface::class)->create($params);

            if (empty($language)) {
                throw new Exception(__('Failed to create language.'));
            }

            $this->languageService->addLanguage($params['short_code']);

            $this->cache($language);

        } catch (Exception $exception) {

            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to create language.'));
        }

        DB::commit();
        return redirect()->route('languages.index')->with(SERVICE_RESPONSE_SUCCESS, __('Language [:lang] has been created successfully.', ['lang' => $params['short_code']]));
    }

    private function cache($language)
    {
        $languages = Cache::get('languages');

        $languages[$language->short_code] = [
            'name' => $language->name,
            'icon' => $language->icon
        ];

        Cache::set('languages', $languages);
    }

    public function edit($id)
    {
        $data['language'] = $this->languageRepository->findOrFailById($id);
        $data['title'] = __('Edit Language');
        return view('backend.languages.edit', $data);
    }

    public function update(LanguageRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $language = $this->languageRepository->getFirstById($id);
            if (empty($language)) {
                throw new Exception(__('Language could not found or invalid.'));
            }

            $params = $request->only(['name', 'short_code', 'is_active']);

            if ($language->short_code == settings('lang')) {
                $params['is_active'] = ACTIVE_STATUS_ACTIVE;
            }

            if ($params['short_code'] != $language->short_code) {
                $isRenamed = $this->languageService->rename($language->short_code, $params['short_code']);
                if (!$isRenamed) {
                    throw new Exception(__('Failed to rename file.'));
                }
            }

            if ($request->hasFile('icon')) {
                $filePath = config('commonconfig.language_icon');
                $fileName = $params['short_code'];
                $params['icon'] = app(FileUploadService::class)->upload($request->file('icon'), $filePath, $fileName, $prefix = '', $suffix = '', $disk = 'public', $width = 120, $height = 80);
            }

            $language = $this->languageRepository->update($params, $id);

            if (empty($language)) {
                throw new Exception(__('Failed to update language.'));
            }

            $this->cache($language);

        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, $exception->getMessage());
        }
        DB::commit();

        return redirect()->route('languages.index')->with(SERVICE_RESPONSE_SUCCESS, __('Language [:lang] has been updated successfully.', ['lang' => $params['short_code']]));

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $conditions = ['id' => $id, ['short_code', '!=', settings('lang')]];
            $language = $this->languageRepository->deleteByConditions($conditions);

            if (!$language) {
                throw new Exception(__('Default language cannot be deleted.'));
            }

            if (!$this->languageService->delete($language->short_code)) {
                throw new Exception(__('Failed to delete file.'));
            }

            $languages = Cache::get('languages');
            unset($languages[$language->short_code]);
            Cache::set('languages', $languages);

        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->route('languages.index')->with(SERVICE_RESPONSE_ERROR, $exception->getMessage());
        }

        DB::commit();
        return redirect()->route('languages.index')->with(SERVICE_RESPONSE_SUCCESS, __('Language [:lang] has been deleted successfully.', ['lang' => $language->short_code]));
    }

    public function settings()
    {
        $data['title'] = __('Language Settings');
        return view('backend.languages.settings', $data);
    }

    public function getTranslation()
    {
        $translations = $this->languageService->getTranslations();
        return response()->json($translations);
    }

    public function settingsUpdate(Request $request)
    {
        $this->languageService->saveTranslations($request->translations);
        return response()->json(['type' => 'success', 'message' => __('Saved successfully.')]);
    }

    public function sync()
    {
        $response = $this->languageService->sync();
        return response()->json($response);
    }
}
