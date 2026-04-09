<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Controllers;

use App\Application\Exceptions\EcommerceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Controller base com tratamento de erros padronizado
 */
abstract class BaseController
{
    /**
     * Executa uma operação com tratamento de erros
     */
    protected function execute(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (EcommerceException $e) {
            // Erros de negócio (esperados) - retorna para o cliente
            return $this->errorResponse(
                message: $e->getError()->getMessage(),
                code: $e->getError()->getCode(),
            );
        } catch (ValidationException $e) {
            // Erros de validação do Laravel
            return $this->errorResponse(
                message: 'Erro de validação',
                code: 422,
                errors: $e->errors(),
            );
        } catch (\InvalidArgumentException $e) {
            // Erros de validação da entidade (regras de negócio)
            return $this->errorResponse(
                message: $e->getMessage(),
                code: 400,
            );
        } catch (\Exception $e) {
            // Erros inesperados do sistema - loga e retorna mensagem genérica
            Log::error('Erro inesperado no sistema', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                message: 'Ocorreu um erro interno no servidor. Por favor, tente novamente mais tarde.',
                code: 500,
            );
        }
    }

    /**
     * Retorna resposta de sucesso padronizada
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operação realizada com sucesso',
        int $code = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Retorna resposta de erro padronizada
     */
    protected function errorResponse(
        string $message,
        int $code = 400,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
