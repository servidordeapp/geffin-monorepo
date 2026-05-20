import { ScrollView, RefreshControl, StyleSheet, Text, View } from 'react-native'
import { SafeAreaView } from 'react-native-safe-area-context'
import { Skeleton } from '@gfn/design-system'
import { ErrorState } from '@gfn/design-system'
import { colors } from '@gfn/design-system/tokens'
import { useGuardianDashboard } from '../../hooks/useGuardianDashboard'
import { ChildCarousel } from '../../components/ChildCarousel'
import { QuickAccessGrid } from '../../components/QuickAccessGrid'
import { DueItemCard } from '../../components/DueItemCard'
import { ActivityRow } from '../../components/ActivityRow'
import { OfflineBanner } from '../../components/OfflineBanner'

export default function DashboardScreen() {
  const { guardian, children, payments, recentActivity, isLoading, isError, refetch } =
    useGuardianDashboard()

  if (isError) {
    return (
      <SafeAreaView style={styles.container}>
        <ErrorState title="Erro ao carregar dados" onRetry={refetch} />
      </SafeAreaView>
    )
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <OfflineBanner />
      <ScrollView
        testID="dashboard-scroll"
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={isLoading}
            onRefresh={refetch}
            tintColor={colors.brand.primary700}
          />
        }
      >
        {/* Header greeting */}
        <View style={styles.greeting}>
          <Text style={styles.greetingText}>
            {guardian ? `Olá, ${guardian.firstName}!` : 'Carregando...'}
          </Text>
        </View>

        {/* Children carousel */}
        <View style={styles.section}>
          {isLoading ? (
            <View style={{ height: 200 }}>
              <Skeleton className="w-full h-full rounded-xl" />
            </View>
          ) : (
            <ChildCarousel children={children} />
          )}
        </View>

        {/* Quick access */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Acesso Rápido</Text>
          {isLoading ? (
            <View style={styles.skeletonRow}>
              {[0, 1, 2, 3].map((i) => (
                <Skeleton key={i} className="flex-1 h-20 rounded-lg" />
              ))}
            </View>
          ) : (
            <QuickAccessGrid />
          )}
        </View>

        {/* Due payments */}
        {(isLoading || payments.length > 0) && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>A pagar este mês</Text>
            {isLoading ? (
              <View style={{ gap: 8 }}>
                {[0, 1].map((i) => <Skeleton key={i} className="w-full h-16 rounded-lg" />)}
              </View>
            ) : (
              <View style={{ gap: 8 }}>
                {payments.map((p) => <DueItemCard key={p.id} payment={p} />)}
              </View>
            )}
          </View>
        )}

        {/* Recent activity */}
        {(isLoading || recentActivity.length > 0) && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Atividade Recente</Text>
              <Text style={styles.seeAll}>Ver tudo</Text>
            </View>
            {isLoading ? (
              <View style={{ gap: 4 }}>
                {[0, 1, 2].map((i) => <Skeleton key={i} className="w-full h-14" />)}
              </View>
            ) : (
              recentActivity.slice(0, 5).map((a) => <ActivityRow key={a.id} activity={a} />)
            )}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  )
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.neutral[50] },
  scrollContent: { gap: 24, paddingHorizontal: 24, paddingBottom: 40, paddingTop: 16 },
  greeting: { marginBottom: -8 },
  greetingText: { fontSize: 22, fontWeight: '700', color: colors.brand.primary900 },
  section: { gap: 12 },
  sectionTitle: { fontSize: 16, fontWeight: '600', color: colors.neutral[900] },
  sectionHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  seeAll: { fontSize: 14, color: colors.brand.primary700, fontWeight: '500' },
  skeletonRow: { flexDirection: 'row', gap: 8 },
})
