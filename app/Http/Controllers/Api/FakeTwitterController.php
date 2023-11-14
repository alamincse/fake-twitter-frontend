<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\ValidationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\TweetLike;
use App\Models\Follower;
use App\Models\Tweet;
use Carbon\Carbon;

class FakeTwitterController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $service = new ValidationService();
        $validator = $service->validateLogin($request);

        if ($validator->fails()) {
            return app(ApiResponse::class)->validationError($validator->errors()->toArray());
        }

        try {
            $credentials = $request->only(['email', 'password']);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                $data['name'] = data_get($user, 'name');
                $data['email'] = data_get($user, 'email');
                $data['access_token'] = $user->createToken('AuthToken')->accessToken;

                Log::info('Login Success');

                return app(ApiResponse::class)
                    ->success($data, 'Login success.')
                    ->cookie('twitter_cookie', $data['access_token']);
            }


            return app(ApiResponse::class)->error('Your email or password does not match our record!');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong.' . $e->getMessage());
        }
    }

    public function register(Request $request)
    {
        $service = new ValidationService();

        $validator = $service->validateRegister($request);

        if ($validator->fails()) {
            return app(ApiResponse::class)->validationError($validator->errors()->toArray());
        }

        try {
            $user = $service->doRegister($request);


            Log::info('Registration Success');

            return app(ApiResponse::class)->success($user, 'You have successfully registration.');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Registration Failed.' . $e->getMessage());
        }

    }

    public function profile()
    {
        try {
            $user = auth()->user();

            // Following count by 'following_count'!
            $user->loadCount('following');

            if (! blank($user)) {
                $user->join_date = $this->getDate(data_get($user, 'created_at'));
                $user->profile_picture = data_get($user, 'profile') ? url('storage/profile_picture/' . data_get($user, 'profile')) : '';
                $user->timeline_picture = data_get($user, 'timeline') ? url('storage/timeline_picture/' . data_get($user, 'timeline')) : '';
            }

            return app(ApiResponse::class)->success($user, 'Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function updateProfile(Request $request)
    {
        $service = new ValidationService();

        $user = auth()->user();

        $validator = $service->validateProfile($request, $user->id);

        if ($validator->fails()) {
            return app(ApiResponse::class)->validationError($validator->errors()->toArray());
        }

        try {
            $data = [
                'name' => data_get($request, 'name'),
                'email' => data_get($request, 'email'),
                'password' => Hash::make(data_get($request, 'password')),
                'address' => data_get($request, 'address'),
            ];

            $picture = $request->file('picture');
            $timelinePicture = $request->file('timeline_picture');

            $time = time();

            $oldProfilePicture = data_get($user, 'profile');
            $oldTimelinePicture = data_get($user, 'timeline');

            if (! blank($picture)) {
                $picture->storeAs('profile_picture', $time . $picture->getClientOriginalName(), 'public');

                $data['profile'] = $time . $picture->getClientOriginalName();

                $profilePath = public_path('storage/profile_picture/'.$oldProfilePicture);

                if(! blank($oldProfilePicture) && File::exists($profilePath)){
                    unlink($profilePath);
                }
            }

            if (! blank($timelinePicture)) {
                $timelinePicture->storeAs('timeline_picture', $time . $timelinePicture->getClientOriginalName(), 'public');

                $data['timeline'] = $time . $timelinePicture->getClientOriginalName();

                $timelinePath = public_path('storage/timeline_picture/'.$oldTimelinePicture);

                if(! blank($oldTimelinePicture) && File::exists($timelinePath)){
                    unlink($timelinePath);
                }
            }


            $profile = $user->update($data);


            Log::info('Profile Update Success');

            return app(ApiResponse::class)->success($profile, 'You have successfully updated your profile.');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Profile Update Failed.' . $e->getMessage());
        }
    }

    public function postTweet(Request $request)
    {
        $service = new ValidationService();

        $validator = $service->validateTweet($request);

        if ($validator->fails()) {
            return app(ApiResponse::class)->validationError($validator->errors()->toArray());
        }

        try {
            $data = [
                'participant_id' => $request->user()->id,
                'tweet' => data_get($request, 'tweet'),
            ];

            $tweet = Tweet::create($data);


            Log::info('Tweet Success');

            return app(ApiResponse::class)->success($tweet, 'Tweet Success.');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Tweet Failed.' . $e->getMessage());
        }
    }

    public function getMyTweets()
    {
        try {
            $participantId = auth()->user()->id;

            $tweets = Tweet::withCount('likes')
                        ->with(['participant', 'likes'])
                        ->where('participant_id', $participantId)
                        ->latest()
                        ->get();

            if (! blank($tweets)) {
                $tweets->map(function ($item) use ($participantId) {
                    $participantIds = $item->likes()->pluck('participant_id')->toArray();

                    $item->like_status = 'No';
                    $item->date = $this->getDate(data_get($item, 'created_at'));
                    $item->profile_picture = data_get($item, 'participant.profile') ? url('storage/profile_picture/' .data_get($item, 'participant.profile')) : '';

                    if (in_array($participantId, $participantIds)) {
                        $item->like_status = 'Yes';
                    }

                    return $item;
                });
            }


            return app(ApiResponse::class)->success($tweets, 'Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function getTweets()
    {
        try {
            $participantId = auth()->user()->id;

            // get participant ids where I am following
            $followingIds = Follower::where('follower_id', $participantId)->pluck('participant_id')->toArray();

            $followingIds[] = $participantId;

            $tweets = Tweet::withCount('likes')
                            ->with(['participant', 'likes'])
                            ->whereIn('participant_id', $followingIds)
                            ->latest()
                            ->get();

            if (! blank($tweets)) {
                $tweets->map(function ($item) use ($participantId) {
                    $participantIds = $item->likes()->pluck('participant_id')->toArray();

                    $item->profile_picture = data_get($item, 'participant.profile') ? url('storage/profile_picture/' . data_get($item, 'participant.profile')) : '';

                    $item->like_status = 'No';
                    $item->date = $this->getDate(data_get($item, 'created_at'));

                    if (in_array($participantId, $participantIds)) {
                        $item->like_status = 'Yes';
                    }

                    return $item;
                });
            }


            return app(ApiResponse::class)->success($tweets, 'Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function doLike(Request $request)
    {
        try {
            $participantId = auth()->user()->id;
            $tweetId = data_get($request, 'tweet_id');

            $tweet = Tweet::findOrFail($tweetId);

            $like = '';

            if (! blank($tweet)) {
               $tweet->increment('total_likes');

               $like = TweetLike::create([
                    'tweet_id' => $tweetId,
                    'participant_id' => $participantId,
               ]);
            }

            return app(ApiResponse::class)->success($like, 'Success');

        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function doUnlike(Request $request)
    {
        try {
            $participantId = auth()->user()->id;
            $tweetId = data_get($request, 'tweet_id');

            $tweet = Tweet::findOrFail($tweetId);


            $unLike = '';

            if (! blank($tweet)) {
                $tweet->decrement('total_likes');

                $unLike = TweetLike::where([
                        ['tweet_id', '=', $tweetId],
                        ['participant_id', '=', $participantId],
                    ])
                    ->first()
                    ->delete();

            }


            return app(ApiResponse::class)->success($unLike, 'Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function doFollow(Request $request)
    {
        try {
            $followerId = auth()->user()->id;
            $participantId = data_get($request, 'participant_id');

            $participant = Participant::findOrFail($participantId);

            $follow = '';

            if (! blank($participant)) {
                $participant->increment('total_follower');

                $follow = Follower::create([
                    'follower_id' => $followerId,
                    'participant_id' => $participantId,
                ]);
            }

            return app(ApiResponse::class)->success($follow, 'Following Success');

        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function doUnfollow(Request $request)
    {
        try {
            $followerId = auth()->user()->id;
            $participantId = data_get($request, 'participant_id');

            $participant = Participant::findOrFail($participantId);

            $unFollow = '';

            if (! blank($participant)) {
                $participant->decrement('total_follower');

                $unFollow = Follower::where([
                        ['follower_id', '=', $followerId],
                        ['participant_id', '=', $participantId],
                    ])
                    ->first()
                    ->delete();

            }

            return app(ApiResponse::class)->success($unFollow, 'Unfollow Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function search(Request $request)
    {
        try {
            $participantId = auth()->user()->id;

            $email = data_get($request, 'email');

            $participant = Participant::with('followers')
                                ->active()
                                ->where('email', $email)
                                ->first();

            if (! blank($participant)) {
                $participant->join_date = $this->getDate(data_get($participant, 'created_at'));
                $participant->profile_picture = data_get($participant, 'profile') ? url('storage/profile_picture/' . data_get($participant, 'profile')) : '';
            }

            $followerIds = $participant->followers()->pluck('follower_id')->toArray();

            $participant->is_following = 'No';

            if (in_array($participantId, $followerIds)) {
                $participant->is_following = 'Yes';
            }

            $tweets = Tweet::withCount('likes')
                        ->with(['participant', 'likes'])
                        ->where('participant_id', data_get($participant, 'id'))
                        ->latest()
                        ->get();

            if (! blank($tweets)) {
                $tweets->map(function ($item) use ($participantId) {
                    $participantIds = $item->likes()->pluck('participant_id')->toArray();

                    $item->date = $this->getDate(data_get($item, 'created_at'));
                    $item->profile_picture = data_get($item, 'participant.profile') ? url('storage/profile_picture/' . data_get($item, 'participant.profile')) : '';


                    $item->like_status = 'No';

                    if (in_array($participantId, $participantIds)) {
                        $item->like_status = 'Yes';
                    }

                    return $item;
                });
            }


            $data = [
                'participant' => $participant,
                'tweets' => $tweets,
            ];

            return app(ApiResponse::class)->success($data, 'Success');

        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function getFollowers()
    {
        try {
            $user = auth()->user();

            $user->load('followers.follower');

            if (! blank($user)) {
                $user->join_date = $this->getDate(data_get($user, 'created_at'));

                if (! blank($user->followers)) {
                    $user->followers->map(function ($item) {
                        $item->profile_picture = data_get($item, 'follower.profile') ? url('storage/profile_picture/'.data_get($item, 'follower.profile')) : '';

                        return $item;
                    });
                }
            }

            return app(ApiResponse::class)->success($user, 'Follower List Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    public function getFollowing()
    {
        try {
            $user = auth()->user();

            $user->load('following.participant');

            if (! blank($user)) {
                $user->join_date = $this->getDate(data_get($user, 'created_at'));

                if (! blank($user->following)) {
                    $user->following->map(function ($item) {
                        $item->profile_picture = data_get($item, 'participant.profile') ? url('storage/profile_picture/'.data_get($item, 'participant.profile')) : '';

                        return $item;
                    });
                }
            }

            return app(ApiResponse::class)->success($user, 'Following List Success');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }

    }

    public function refreshToken()
    {
        try {
            $token = auth()->user()->token();

            $token->revoke();


            $newToken = auth()->user()->createToken('AuthToken')->accessToken;

            $data['name'] = data_get(auth()->user(), 'name');
            $data['email'] = data_get(auth()->user(), 'email');
            $data['access_token'] = $newToken;

            Log::info('New Token Success');

            return app(ApiResponse::class)->success($data, 'Login back & successfully generate new token.');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong.' . $e->getMessage());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $delete = $user->token()->revoke();

            return app(ApiResponse::class)
                ->success($delete, 'Successfully logout')
                ->cookie('twitter_cookie', null, -1, '/');
        } catch (\Exception $e) {
            Log::error($e);

            return app(ApiResponse::class)->error('Something went wrong, Please try again.');
        }
    }

    private function getDate($date): string
    {
        return Carbon::parse($date)->format('jS F Y, h:i:s A');
    }
}
