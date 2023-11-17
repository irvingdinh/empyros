interface Props {
  version: string
}

export default function Page ({ version }: Props) {
  return (
    <p>Powered by Laravel v{version}</p>
  )
}
