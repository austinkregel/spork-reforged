<?php

namespace Integration\Repositories;

use App\Models\Credential;
use App\Models\Person;
use App\Models\User;
use App\Repositories\MatrixClientSyncRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrixClientSyncRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MatrixClientSyncRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MatrixClientSyncRepository();
    }

    public function testProcessRoom()
    {
        $this->assertDatabaseEmpty('threads');
        $this->assertDatabaseEmpty('people');
        $this->assertDatabaseEmpty('messages');

        $roomState = json_decode(json_encode(
            [
                "timeline" => [
                    "events" => [
                        [
                            "type" => "m.room.message",
                            "sender" => "@test:fake.tools",
                            "content" => [
                            "body" => "My local dispo is now selling packs of rerolls like one might sell a pack of cigarettes. Contains 10 .7g pre rolls",
                                "com.beeper.linkpreviews" => [],
                                "com.beeper.per_message_profile" => [
                                "avatar_url" => "mxc:\/\/fake.tools\/SewLGoywEaRuTCbsdsCOOeMH",
                                    "displayname" => "Baldy",
                                    "is_multiple_users" => false
                                ],
                                "fi.mau.discord.guild_member_metadata" => [
                                "avatar_id" => "",
                                    "avatar_mxc" => "",
                                    "avatar_url" => "",
                                    "nick" => "Baldy"
                                ],
                                "fi.mau.double_puppet_source" => "mautrix-discord",
                                "m.mentions" => [],
                                "msgtype" => "m.text"
                            ],
                            "origin_server_ts" =>1730322807392,
                            "unsigned" => [
                            "membership" => "join",
                            "age" =>3579117058,
                            "m.relations" => [
                                "m.replace" => [
                                    "type" => "m.room.message",
                                        "sender" => "@test:fake.tools",
                                        "content" => [
                                        "body" => "* My local dispo is now selling packs of prerolls like one might sell a pack of cigarettes. Contains 10 .7g pre rolls",
                                            "fi.mau.double_puppet_source" => "mautrix-discord",
                                            "m.mentions" => [],
                                            "m.new_content" => [
                                            "body" => "My local dispo is now selling packs of prerolls like one might sell a pack of cigarettes. Contains 10 .7g pre rolls",
                                                "com.beeper.linkpreviews" => [],
                                                "com.beeper.per_message_profile" => [
                                                "avatar_url" => "mxc:\/\/fake.tools\/SewLGoywEaRuTCbsdsCOOeMH",
                                                    "displayname" => "Baldy",
                                                    "is_multiple_users" =>false
                                                ],
                                                "fi.mau.discord.guild_member_metadata" => [
                                                "avatar_id" => "",
                                                    "avatar_mxc" => "",
                                                    "avatar_url" => "",
                                                    "nick" => "Baldy"
                                                ],
                                                "m.mentions" => [],
                                                "msgtype" => "m.text"
                                            ],
                                            "m.relates_to" => [
                                            "event_id" => "\$PCUcDAr-jsepEwBRsOYjhavcZbPm3yMWTQlFb-hk_CQ",
                                                "rel_type" => "m.replace"
                                            ],
                                            "msgtype" => "m.text"
                                        ],
                                        "origin_server_ts" =>1730322817001,
                                        "unsigned" => [
                                        "age" =>3579115761
                                        ],
                                        "event_id" => "\$q9Ri8dr2cuMb2qt4w5WIo9ltDHxKa_WkaCOI3W_yvo8"
                                    ]
                                ]
                            ],
                            "event_id" => "\$PCUcDAr-jsepEwBRsOYjhavcZbPm3yMWTQlFb-hk_CQ"
                        ],
                        [
                            "type" => "m.room.message",
                            "sender" => "@discord_576234587089534999:fake.tools",
                            "content" => [
                            "body" => "Love the plague doctor \ud83d\ude02",
                                "com.beeper.linkpreviews" => [],
                                "fi.mau.discord.guild_member_metadata" => [
                                "avatar_id" => "",
                                    "avatar_mxc" => "",
                                    "avatar_url" => "",
                                    "nick" => ""
                                ],
                                "m.mentions" => [],
                                "msgtype" => "m.text"
                            ],
                            "origin_server_ts" =>1730323218841,
                            "unsigned" => [
                            "membership" => "join",
                                "age" =>3578715065
                            ],
                            "event_id" => "\$usqb5x4ffMASdDVCcAxk2UZ8zVsrbK4GKwRq1JBhfIo"
                        ],
                        [
                            "type" => "m.room.message",
                            "sender" => "@test:fake.tools",
                            "content" => [
                                "msgtype" => "m.text",
                                "body" => "I know"
                            ],
                            "origin_server_ts" => 1712324421492,
                            "unsigned" => [
                                "membership" => "join",
                                "age" => 21577512374
                            ],
                            "event_id" => "\$laz5pm5kSM6wEFALbV4HRWpZYyuheQqYtHFSBYPzEz4"
                        ],
                    ],
                    "prev_batch" => "s51506_4332798_794_41215_18067_8_180_70665_0_4",
                    "limited" =>true
                ],
                "state" => [
                    "events" => [
                        [
                            "type" => "m.bridge",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "bridgebot" => "@discordbot:fake.tools",
                                "creator" => "@discordbot:fake.tools",
                                "protocol" => [
                                "id" => "discordgo",
                                    "displayname" => "Discord",
                                    "avatar_url" => "mxc:\/\/maunium.net\/nIdEykemnwdisvHbpxflpDlC",
                                    "external_url" => "https:\/\/discord.com\/"
                                ],
                                "network" => [
                                "id" => "654835584996737035",
                                    "displayname" => "Give me Top Golf or give me death",
                                    "avatar_url" => "mxc:\/\/fake.tools\/otbfEzBumMKEpuUfPCKhTUgn"
                                ],
                                "channel" => [
                                "id" => "677166717629235220",
                                    "displayname" => "#narcos",
                                    "external_url" => "https:\/\/discord.com\/channels\/654835584996737035\/677166717629235220"
                                ]
                            ],
                            "state_key" => "fi.mau.discord:\/\/discord\/654835584996737035\/677166717629235220",
                            "origin_server_ts" =>1703001418043,
                            "unsigned" => [
                            "age" =>30900516130
                            ],
                            "event_id" => "\$zjP5hpSeeMSzYTYlh1bW83uHarYJcbeuENbBo1aYCg0"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "Discord bridge bot",
                                "avatar_url" => "mxc:\/\/maunium.net\/nIdEykemnwdisvHbpxflpDlC"
                            ],
                            "state_key" => "@discordbot:fake.tools",
                            "origin_server_ts" =>1703001417466,
                            "unsigned" => [
                            "age" =>30900516707
                            ],
                            "event_id" => "\$-DEh23Ck0uOqW27i0Z8NTzWMwAzgApCZ1wzdpJPPL94"
                        ],
                        [
                            "type" => "m.room.create",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "room_version" => "10",
                                "creator" => "@discordbot:fake.tools"
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001416953,
                            "unsigned" => [
                            "age" =>30900517220
                            ],
                            "event_id" => "\$BSgzP63zk4MibOem93uPBr2D_AiV71_a9G3JPWPcBlg"
                        ],
                        [
                            "type" => "m.room.join_rules",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "join_rule" => "restricted",
                                "allow" => [
                                    [
                                        "room_id" => "!uPvxzEptEwHcWeYxoc:fake.tools",
                                        "type" => "m.room_membership"
                                    ]
                                ]
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001418052,
                            "unsigned" => [
                            "age" =>30900516121
                            ],
                            "event_id" => "\$Syocg5BmGk9KL7oeajmx8dnASsodU1lAF0biu_Ipnb0"
                        ],
                        [
                            "type" => "m.space.parent",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "via" => [
                                "fake.tools"
                            ],
                                "canonical" =>true
                            ],
                            "state_key" => "!uPvxzEptEwHcWeYxoc:fake.tools",
                            "origin_server_ts" =>1703001418049,
                            "unsigned" => [
                            "age" =>30900516124
                            ],
                            "event_id" => "\$1xjNnIQX0jLtBGz2jxbrBji8sn_PRqwF5fW_ky_9Brw"
                        ],
                        [
                            "type" => "m.room.history_visibility",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "history_visibility" => "shared"
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001418038,
                            "unsigned" => [
                            "age" =>30900516135
                            ],
                            "event_id" => "\$4BGKfT9Jg2hG3kreweelEZp_cEKHmmMcqVP8hRBrYaE"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discord_341743122906087444:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "Dynamo",
                                "avatar_url" => "mxc:\/\/discord-media.mau.dev\/avatars|341743122906087444|5ef27c02facc464b1b7d9faac2589aee.png"
                            ],
                            "state_key" => "@discord_341743122906087444:fake.tools",
                            "origin_server_ts" =>1709647637820,
                            "unsigned" => [
                            "replaces_state" => "\$Bw_XS2BF3FFLo987sCfavxpjgMSA4Qeq0-WamSavjJ8",
                                "age" =>24254296353
                            ],
                            "event_id" => "\$3hEDPxlOG5Gp30q1uXq6JekBNMIaM7L7JkNJkEedNqU"
                        ],
                        [
                            "type" => "uk.half-shot.bridge",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "bridgebot" => "@discordbot:fake.tools",
                                "creator" => "@discordbot:fake.tools",
                                "protocol" => [
                                "id" => "discordgo",
                                    "displayname" => "Discord",
                                    "avatar_url" => "mxc:\/\/maunium.net\/nIdEykemnwdisvHbpxflpDlC",
                                    "external_url" => "https:\/\/discord.com\/"
                                ],
                                "network" => [
                                "id" => "654835584996737035",
                                    "displayname" => "Give me Top Golf or give me death",
                                    "avatar_url" => "mxc:\/\/fake.tools\/otbfEzBumMKEpuUfPCKhTUgn"
                                ],
                                "channel" => [
                                "id" => "677166717629235220",
                                    "displayname" => "#narcos",
                                    "external_url" => "https:\/\/discord.com\/channels\/654835584996737035\/677166717629235220"
                                ]
                            ],
                            "state_key" => "fi.mau.discord:\/\/discord\/654835584996737035\/677166717629235220",
                            "origin_server_ts" =>1703001418046,
                            "unsigned" => [
                            "age" =>30900516127
                            ],
                            "event_id" => "\$ywKFXwdssxpoOtYHYsCCD5SFMCA0h8WUNV7NevPEIUg"
                        ],
                        [
                            "type" => "m.room.name",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "name" => "#narcos"
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001418055,
                            "unsigned" => [
                            "age" =>30900516118
                            ],
                            "event_id" => "\$opcOy41sWJucL6_58wntKSk-cs4gJ9kjRCy7VKz0IYQ"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discord_576234587089534999:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "fbomb",
                                "avatar_url" => "mxc:\/\/fake.tools\/XsWjPVIuiaEZMEeCFaezNbQU"
                            ],
                            "state_key" => "@discord_576234587089534999:fake.tools",
                            "origin_server_ts" =>1706659920683,
                            "unsigned" => [
                            "replaces_state" => "\$AS5vaO2iVYd78OMBE83zyZDKrr3CNvCOOQwnOO5Cjm8",
                                "age" =>27242013490
                            ],
                            "event_id" => "\$ROsHi5pvlNTbJ2MIaIzp_6ene78M554ud1lTLKdGDpc"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discord_492745980240855041:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "nwilging",
                                "avatar_url" => "mxc:\/\/fake.tools\/vjBQjoXopFzSoOQVCKDxTCBD"
                            ],
                            "state_key" => "@discord_492745980240855041:fake.tools",
                            "origin_server_ts" =>1706730777344,
                            "unsigned" => [
                            "replaces_state" => "\$33i4mEzG_F0voFr-bRpeqJY3OnY8aYk7DSjXhdHYgDg",
                                "age" =>27171156829
                            ],
                            "event_id" => "\$AooeEgnBKpf_Cdm3csV_JYtcyOg2NaalQmRrxvBI4lU"
                        ],
                        [
                            "type" => "m.room.topic",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "topic" => ""
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703110506487,
                            "unsigned" => [
                            "age" =>30791427686
                            ],
                            "event_id" => "\$zXKWk6dnP3_HZLrGaeDwg5yhQWDgWqaUErDCj-dskjU"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@test:fake.tools",
                            "content" => [
                            "fi.mau.double_puppet_source" => "mautrix-discord",
                                "membership" => "join",
                                "displayname" => "kregel"
                            ],
                            "state_key" => "@test:fake.tools",
                            "origin_server_ts" =>1703001420610,
                            "unsigned" => [
                            "replaces_state" => "\$L2AJcUVWvIGKy7XjPyuYTJ6wCWSESipaZBP-hBhxAqU",
                                "age" =>30900513563
                            ],
                            "event_id" => "\$UM-bdv-7-131PcwY_KJYdFG8f36BXcXllscfK3059Mk"
                        ],
                        [
                            "type" => "m.room.power_levels",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "users" => [
                                "@discordbot:fake.tools" =>100
                                ],
                                "users_default" =>0,
                                "events" => [
                                "m.room.name" =>50,
                                    "m.room.power_levels" =>100,
                                    "m.room.history_visibility" =>100,
                                    "m.room.canonical_alias" =>50,
                                    "m.room.avatar" =>50,
                                    "m.room.tombstone" =>100,
                                    "m.room.server_acl" =>100,
                                    "m.room.encryption" =>100
                                ],
                                "events_default" =>0,
                                "state_default" =>50,
                                "ban" =>50,
                                "kick" =>50,
                                "redact" =>50,
                                "invite" =>0,
                                "historical" =>100
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001417933,
                            "unsigned" => [
                            "age" =>30900516240
                            ],
                            "event_id" => "\$kLVMwLITyTVzP460RNh-XxC3vv6DilIcHRxXpyb68YA"
                        ],
                        [
                            "type" => "m.room.guest_access",
                            "sender" => "@discordbot:fake.tools",
                            "content" => [
                            "guest_access" => "can_join"
                            ],
                            "state_key" => "",
                            "origin_server_ts" =>1703001418040,
                            "unsigned" => [
                            "age" =>30900516133
                            ],
                            "event_id" => "\$J63i4hS6rYppgueZiMWey3WeYKjIL65yhd4Dy_pXKt8"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discord_579771743246483466:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "Feli Wan Kenobi",
                                "avatar_url" => "mxc:\/\/fake.tools\/lUUhWmzzTLWKorHlyscnhAmj"
                            ],
                            "state_key" => "@discord_579771743246483466:fake.tools",
                            "origin_server_ts" =>1712242980272,
                            "unsigned" => [
                            "replaces_state" => "\$Z-ZKi87WLHTE3owxlctScp0dJMGMLohEfJKKz2Ts8RA",
                                "age" =>21658953901
                            ],
                            "event_id" => "\$UqWMq15dIGTZQDkrMfX4lmKhTlDnEYSuClqvqXa0jFo"
                        ],
                        [
                            "type" => "m.room.member",
                            "sender" => "@discord_458751755447042059:fake.tools",
                            "content" => [
                            "membership" => "join",
                                "displayname" => "jpuck",
                                "avatar_url" => "mxc:\/\/fake.tools\/iXJNXeMckLkJUGFMVcmQxCWB"
                            ],
                            "state_key" => "@discord_458751755447042059:fake.tools",
                            "origin_server_ts" =>1710805138704,
                            "unsigned" => [
                            "replaces_state" => "\$IvCy93wXxWuYrCNZmUsfRPh_of8sfR7H_kMrVbeiEDw",
                                "age" =>23096795469
                            ],
                            "event_id" => "\$YjBut9FxZXm0jni5c-itF1RtXFfMXpuuPpkjflcIma4"
                        ]
                    ]
                ],
                "account_data" => [
                    "events" => [
                        [
                            "type" => "m.fully_read",
                            "content" => [
                            "event_id" => "\$K6t_ey4XI84fKw7YUganvNmd6b3IG-UrqxpQUAtYOBI"
                            ]
                        ]
                    ]
                ],
                "ephemeral" => [
        "events" => [
                        [
                            "type" => "m.receipt",
                            "content" => [
                            "\$xmK8Y98mVMfjSK3r32npOXWVWKZBahRTSv0TZ7s2ehM" => [
                                "m.read" => [
                                    "@test:fake.tools" => [
                                        "ts" =>1725388241255,
                                            "thread_id" => "main"
                                        ]
                                    ]
                                ],
                                "\$K6t_ey4XI84fKw7YUganvNmd6b3IG-UrqxpQUAtYOBI" => [
                                "m.read" => [
                                    "@test:fake.tools" => [
                                        "ts" =>1731597331183
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "unread_notifications" => [
                    "notification_count" => 0,
                    "highlight_count" => 0
                ],
                "summary" => []
            ]
        ), true);

        /** @var Credential $credential */
        $credential = Credential::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);
        $user = $credential->user;
        $person = $user->person();
        $person->identifiers = ['@test:fake.tools'];
        $person->save();

        $this->assertDatabaseEmpty('threads');
        $this->assertDatabaseEmpty('thread_participants');
        $this->assertDatabaseCount('people', 1);
        $this->assertDatabaseEmpty('messages');

        $this->repository->processRoom('!room:fake.tools', $roomState, $credential, $user);
        $this->repository->processRoom('!room:fake.tools', $roomState, $credential, $user);
        $this->assertDatabaseCount('thread_participants', 7);

        $this->assertDatabaseCount('threads', 1);
        $this->assertDatabaseCount('people', 7);
        $this->assertDatabaseCount('messages', 3);
    }
}