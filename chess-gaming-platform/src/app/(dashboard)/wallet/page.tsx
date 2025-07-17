"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { formatCurrency, formatDate } from "@/lib/utils"
import { Plus, Minus } from "lucide-react"

export default function WalletPage() {
  const [activeTab, setActiveTab] = useState<"tokens" | "transactions" | "matches">("tokens")

  // Mock data
  const wallet = {
    balance: 5000,
    tokens: 25,
  }

  const tokenTransactions = [
    {
      id: 1,
      tokens: 10,
      note: "Token purchase from balance",
      date: "2024-01-15",
    },
    {
      id: 2,
      tokens: 5,
      note: "Tokens used to create challenge",
      date: "2024-01-14",
    },
  ]

  const transactions = [
    {
      id: 1,
      type: "deposit",
      amount: 1000,
      date: "2024-01-15",
    },
    {
      id: 2,
      type: "withdrawal",
      amount: 500,
      date: "2024-01-14",
    },
  ]

  const matchTransactions = [
    {
      id: 1,
      opponent: "ChessMaster",
      result: "Win",
      tokens: 5,
      amount: 500,
      type: "credit",
      date: "2024-01-15",
    },
    {
      id: 2,
      opponent: "PawnPusher",
      result: "Loss",
      tokens: 3,
      amount: 300,
      type: "debit",
      date: "2024-01-14",
    },
  ]

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">Wallet</h1>

      {/* Balance Card */}
      <Card className="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <CardContent className="p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100">Balance</p>
              <p className="text-3xl font-bold">{formatCurrency(wallet.balance)}</p>
            </div>
            <div className="flex flex-col space-y-2">
              <Button variant="secondary" size="sm">
                <Plus className="mr-2 h-4 w-4" />
                Deposit
              </Button>
              <Button variant="outline" size="sm" className="text-white border-white hover:bg-white hover:text-blue-600">
                <Minus className="mr-2 h-4 w-4" />
                Withdraw
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Tabs */}
      <div className="flex space-x-2">
        <Button
          variant={activeTab === "tokens" ? "default" : "outline"}
          onClick={() => setActiveTab("tokens")}
        >
          Tokens
        </Button>
        <Button
          variant={activeTab === "transactions" ? "default" : "outline"}
          onClick={() => setActiveTab("transactions")}
        >
          Transactions
        </Button>
        <Button
          variant={activeTab === "matches" ? "default" : "outline"}
          onClick={() => setActiveTab("matches")}
        >
          Match History
        </Button>
      </div>

      {/* Token Tab */}
      {activeTab === "tokens" && (
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle>🎟️ Token Balance</CardTitle>
              <CardDescription>Current tokens: {wallet.tokens}</CardDescription>
            </div>
            <Button size="sm">Buy Tokens</Button>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {tokenTransactions.map((tx) => (
                <div key={tx.id} className="flex items-center justify-between border-b pb-2">
                  <div>
                    <p className="font-medium">{tx.tokens} Tokens</p>
                    <p className="text-sm text-muted-foreground">{tx.note}</p>
                  </div>
                  <p className="text-sm text-muted-foreground">{formatDate(tx.date)}</p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Transactions Tab */}
      {activeTab === "transactions" && (
        <Card>
          <CardHeader>
            <CardTitle>Recent Transactions</CardTitle>
            <CardDescription>Your deposit and withdrawal history</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {transactions.map((tx) => (
                <div key={tx.id} className="flex items-center justify-between border-b pb-2">
                  <div>
                    <p className="font-medium">
                      {tx.type === "deposit" ? "Deposit via TinyPesa" : "P2P Withdrawal"}
                    </p>
                    <p className="text-sm text-muted-foreground">{formatDate(tx.date)}</p>
                  </div>
                  <p className={`font-semibold ${tx.type === "deposit" ? "text-green-600" : "text-red-600"}`}>
                    {tx.type === "deposit" ? "+" : "-"}{formatCurrency(tx.amount)}
                  </p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Match History Tab */}
      {activeTab === "matches" && (
        <Card>
          <CardHeader>
            <CardTitle>Match Transactions</CardTitle>
            <CardDescription>Winnings and losses from matches</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {matchTransactions.map((tx) => (
                <div key={tx.id} className="flex items-center justify-between border-b pb-2">
                  <div>
                    <p className="font-medium">vs. {tx.opponent}</p>
                    <p className="text-sm text-muted-foreground">
                      {tx.result} • {tx.tokens} tokens • {formatDate(tx.date)}
                    </p>
                  </div>
                  <p className={`font-semibold ${tx.type === "credit" ? "text-green-600" : "text-red-600"}`}>
                    {tx.type === "credit" ? "+" : "-"}{formatCurrency(tx.amount)}
                  </p>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  )
}