import { Tabs } from 'expo-router'
import { colors } from '@gfn/design-system/tokens'
import { t } from '../../lib/i18n'

export default function AppLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.brand.primary700,
        tabBarInactiveTintColor: colors.neutral[400],
        tabBarStyle: {
          backgroundColor: colors.neutral[0],
          borderTopColor: colors.neutral[200],
          borderTopWidth: 1,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '500',
        },
      }}
    >
      <Tabs.Screen
        name="index"
        options={{ title: 'Início' }}
      />
      <Tabs.Screen
        name="pay"
        options={{ title: 'Pagar' }}
      />
      <Tabs.Screen
        name="history"
        options={{ title: 'Histórico' }}
      />
      <Tabs.Screen
        name="profile"
        options={{ title: 'Perfil' }}
      />
    </Tabs>
  )
}
