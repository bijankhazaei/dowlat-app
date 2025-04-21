<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Sms\Facade\Sms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @OA\Info (
 *     title="Zeenome API",
 *     version="1.0.0",
 *     description="Zeenome API documentation",
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 */
class AuthController extends Controller
{
    /**
     * Sign in to user account.
     *
     * @OA\Post(
     *     path="/api/auth/sign-in",
     *     summary="Sign In",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(property="mobile", type="string", example="09123456789", description="User's mobile number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(property="data", nullable=true, example=null),
     *             @OA\Property(property="messages", example="[]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=false),
     *             @OA\Property(property="data", nullable=true, example=null),
     *             @OA\Property(property="messages", type="string", )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function signIn(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile' => [
                    'required',
                    'regex:/^(\+98|0)?9\d{9}$/',
                ],
            ], [
                'mobile.required' => 'شماره موبایل الزامی است.',
                'mobile.regex' => 'شماره موبایل باید با 09 یا +989 شروع شده و 11 رقم باشد.',
            ]);

            $otp = strval(rand(1000, 9999));
            if (env('APP_ENV', 'production') === 'local') {
                $otp = collect(str_split(substr($data['mobile'], -4)))
                    ->map(fn($digit) => (int) $digit)
                    ->implode('');
            } else {
                $customer = Customer::query()
                    ->where('mobile', $data['mobile'])
                    ->first();
                if ($customer) {
                    $name = $customer->first_name ?? "کاربر";
                    Sms::send("{$name} عزیز، خوش اومدی!
اینم کد ورودت: {$otp}
این کد رو در اختیار شخص دیگه‌ای نزار!

زینوم لانجویتی، هم مسیر شما برای طول عمر بیشتر
@dev.zeenome.ir #{$otp}
لغو11", $data['mobile']);

                } else {
                    Sms::send("دوست عزیز، خوشحالیم که به جمع ما می‌پیوندی!
اینم کد ورودت: {$otp}
این کد رو در اختیار شخص دیگه‌ای نزار!

زینوم لانجویتی، هم مسیر شما برای طول عمر بیشتر
@dev.zeenome.ir #{$otp}
لغو11", $data['mobile']);
                }
            }

            cache()->put($data['mobile'], $otp, now()->addMinutes(5));

            return response()->apiResult();
        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        }
    }

    /**
     * Verify the OTP and authenticate the user.
     *
     * @OA\Post(
     *     path="/api/auth/sign-validate",
     *     summary="Verify OTP",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "otp"},
     *             @OA\Property(property="mobile", type="string", example="09123456789", description="User's mobile number"),
     *             @OA\Property(property="otp", type="string", example="1234", description="One-time password (OTP)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="ok", type="boolean", example=true),
     *             @OA\Property(
     *                property="data",
     *                type="object",
     *             @OA\Property(property="customer", type="object"),
     *             @OA\Property(property="token", type="string", example="access-token")
     *             ),
     *             @OA\Property(property="messages", example="[]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid OTP"
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function signValidate(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile' => [
                    'required',
                    'regex:/^(\+98|0)?9\d{9}$/',
                ],
                'otp' => 'required|digits:4',
            ], [
                'mobile.required' => 'شماره موبایل الزامی است.',
                'mobile.regex' => 'شماره موبایل باید با 09 یا +989 شروع شده و 11 رقم باشد.',
                'otp.required' => 'کد تایید الزامی است.',
                'otp.digits' => 'کد تایید باید 4 رقم باشد.',
            ]);

            $otp = cache()->get($data['mobile']);

            // if is not production 4 digit of end of mobile
            if (env('APP_ENV') === "local") {
                $codeValidation = $otp == substr($data['mobile'], -4);
            } else {
                $codeValidation = $otp == $data['otp'];
            }

            if (!$codeValidation) {
                return response()->apiResult(statusCode: 422, messages: ['کد صحیح نیست']);
            }

            try {
                $customer = Customer::query()->firstOrCreate(
                    [
                        'mobile' => $data['mobile']
                    ],
                    [
                        'is_registered' => true,
                    ]
                );

                $token = $customer->createToken('auth_token')->accessToken;

                return response()->apiResult([
                    'customer' => $customer,
                    'token' => $token,
                ]);
            } catch (ContainerExceptionInterface | \Exception $e) {
                return response()->apiResult(statusCode: 500, metadata: $e->__tostring());
            }

        } catch (ValidationException $e) {
            return response()->apiResult(statusCode: 422, messages: $e->errors());
        }
    }

    /**
     * Logout the authenticated user.
     *
     * @OA\Post(
     *     path="/api/auth/sign-out",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function signOut(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->apiResult(messages: ['Logged out successfully']);
    }
}
