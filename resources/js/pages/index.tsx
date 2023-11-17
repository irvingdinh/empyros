import { type ReactElement } from 'react'

interface Props {
  version: string
}

export default function Page ({ version }: Props): ReactElement {
  return (
    <p>Powered by Laravel v{version}</p>
  )
}
