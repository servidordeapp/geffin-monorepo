import { I18n } from 'i18n-js'
import { ptBR } from '../locales/pt-BR'

const i18n = new I18n({ 'pt-BR': ptBR, pt: ptBR })
i18n.locale = 'pt-BR'
i18n.enableFallback = true

export default i18n
export const t = (key: string, options?: Record<string, unknown>) => i18n.t(key, options)
