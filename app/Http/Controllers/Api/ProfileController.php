<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="Profile management endpoints"
 * )
 */
class ProfileController extends Controller
{
    /**
     * Get authenticated customer details.
     *
     * @OA\Get(
     *     path="/api/auth/customer",
     *     summary="Get customer details",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Returns customer details",
     *         @OA\JsonContent(
     *             @OA\Property(property="customer", type="object")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        return response()->apiResult($request->user());
    }

    /**
     * Update the user profile.
     *
     * @OA\Put(
     *     path="/api/auth/update-profile",
     *     summary="Update user profile",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "mobile", "national_code"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="mobile", type="string", example="09123456789"),
     *             @OA\Property(property="national_code", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setProfile(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'national_code' => [
                    'required',
                    'string',
                    Rule::unique('customers', 'national_code')->ignore($request->user()->id, 'id')
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('customers', 'email')->ignore($request->user()->id, 'id')
                ],
                'gender' => 'required|string|in:male,female',
                'birthdate' => 'required|date',
                'address' => 'nullable|string',
                'postal_code' => 'nullable|string',
            ]);

            $customer = $request->user();
            $customer = $customer->update($data);

            return response()->apiResult($customer, 201);
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        }
    }
}
