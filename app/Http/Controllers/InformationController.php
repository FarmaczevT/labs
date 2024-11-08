<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DTO\ServerInfoDTO;
use App\DTO\ClientInfoDTO;
use App\DTO\DatabaseInfoDTO;

class InformationController extends Controller
{
    public function getServerInfo() {
        $dto = new ServerInfoDTO(phpversion(), php_uname());
        return response()->json($dto);
     } 

    // Метод для получения информации о клиенте
    public function getClientInfo(Request $request) {
        // Создаем объект DTO, передаем туда IP и User-Agent клиента
        $dto = new ClientInfoDTO($request->ip(), $request->header('user-agent'), $request->header('sec-ch-ua'), $request -> server('DOCUMENT_ROOT'));

        // Возвращаем DTO в формате JSON
        return response()->json($dto);
    }

    // Метод для получения информации о базе данных
    public function getDatabaseInfo() {
        // Получаем подключение к базе данных
        $connection = DB::connection();

        // Создаем объект DTO, передаем туда имя базы данных и драйвер
        $dto = new DatabaseInfoDTO($connection->getDatabaseName(), $connection->getDriverName());

        // Возвращаем DTO в формате JSON
        return response()->json($dto);
    }
}
