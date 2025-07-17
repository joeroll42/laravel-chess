"use client"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Home, Gamepad2, Wallet, Settings } from "lucide-react"
import Link from "next/link"
import { usePathname } from "next/navigation"

const navigation = [
  {
    name: "Dashboard",
    href: "/dashboard",
    icon: Home,
  },
  {
    name: "Matches",
    href: "/matches",
    icon: Gamepad2,
  },
  {
    name: "Wallet",
    href: "/wallet",
    icon: Wallet,
  },
  {
    name: "Settings",
    href: "/settings",
    icon: Settings,
  },
]

export function MobileNav() {
  const pathname = usePathname()

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 bg-background border-t md:hidden">
      <div className="grid grid-cols-4 gap-1 p-2">
        {navigation.map((item) => (
          <Button
            key={item.name}
            variant={pathname === item.href ? "secondary" : "ghost"}
            size="sm"
            className="flex flex-col h-auto py-2"
            asChild
          >
            <Link href={item.href}>
              <item.icon className="h-4 w-4 mb-1" />
              <span className="text-xs">{item.name}</span>
            </Link>
          </Button>
        ))}
      </div>
    </div>
  )
}