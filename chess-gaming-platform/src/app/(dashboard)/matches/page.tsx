"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { formatCurrency } from "@/lib/utils"
import Link from "next/link"

export default function MatchesPage() {
  const [activeTab, setActiveTab] = useState<"available" | "my-challenges">("available")

  // Mock data
  const availableMatches = [
    {
      id: 1,
      username: "ChessMaster",
      rank: 1220,
      tokens: 5,
      stake: 500,
      timeControl: "5+0 Blitz",
      online: true,
    },
    {
      id: 2,
      username: "PawnPusher",
      rank: 1180,
      tokens: 3,
      stake: 300,
      timeControl: "3+2 Blitz",
      online: true,
    },
  ]

  const myChallenges = [
    {
      id: 1,
      opponent: "KnightRider",
      tokens: 4,
      stake: 400,
      timeControl: "10+0 Rapid",
      status: "pending",
      challengeStatus: "pending",
    },
    {
      id: 2,
      opponent: "BishopBeast",
      tokens: 6,
      stake: 600,
      timeControl: "5+0 Blitz",
      status: "accepted",
      challengeStatus: "won",
    },
  ]

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Matches</h1>
        <Button asChild>
          <Link href="/matches/create">Create Challenge</Link>
        </Button>
      </div>

      {/* Tabs */}
      <div className="flex space-x-2">
        <Button
          variant={activeTab === "available" ? "default" : "outline"}
          onClick={() => setActiveTab("available")}
        >
          Available Matches
        </Button>
        <Button
          variant={activeTab === "my-challenges" ? "default" : "outline"}
          onClick={() => setActiveTab("my-challenges")}
        >
          My Challenges
        </Button>
      </div>

      {/* Available Matches */}
      {activeTab === "available" && (
        <div className="space-y-4">
          {availableMatches.map((match) => (
            <Card key={match.id}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <h3 className="font-semibold">{match.username}</h3>
                    <p className="text-sm text-muted-foreground">
                      Rank: {match.rank} • 🎟️ {match.tokens} tokens • {formatCurrency(match.stake)}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Time Control: {match.timeControl}
                    </p>
                  </div>
                  <div className="flex flex-col items-end space-y-2">
                    <Badge variant={match.online ? "default" : "secondary"}>
                      {match.online ? "Online" : "Offline"}
                    </Badge>
                    <Button size="sm">Challenge</Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* My Challenges */}
      {activeTab === "my-challenges" && (
        <div className="space-y-4">
          {myChallenges.map((challenge) => (
            <Card key={challenge.id}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <h3 className="font-semibold">vs. {challenge.opponent}</h3>
                    <p className="text-sm text-muted-foreground">
                      🎟️ {challenge.tokens} tokens • {formatCurrency(challenge.stake)}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Time Control: {challenge.timeControl}
                    </p>
                  </div>
                  <div className="flex flex-col items-end space-y-2">
                    <Badge
                      variant={
                        challenge.status === "pending"
                          ? "secondary"
                          : challenge.status === "accepted"
                          ? "default"
                          : "destructive"
                      }
                    >
                      {challenge.status}
                    </Badge>
                    <Badge
                      variant={
                        challenge.challengeStatus === "pending"
                          ? "secondary"
                          : challenge.challengeStatus === "won"
                          ? "default"
                          : "destructive"
                      }
                    >
                      {challenge.challengeStatus}
                    </Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}