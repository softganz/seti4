<h3>Project TOR</h3>
<nav><a href="{guides}">Guides</a> / <a href="{module}">Module</a> / <a href="{module/project}">Project</a></nav>

<h4>List Project TOR</h4>
<p>List of project TOR</p>
<code>http://domain.name/project/tor</code>

<h4>View Project TOR</h4>
<p>View project TOR of $tpid</p>
<code>http://domain.name/project/tor/{$tpid}</code>

<h4>Create Project TOR</h4>
<p>Create project TOR of $tpid</p>
<code>http://domain.name/project/tor/{$tpid}/create</code>

<h4>Remove Project TOR</h4>
<p>Remove project TOR of $tpid</p>
<code>http://domain.name/project/tor/{$tpid}/remove?confirm=yes</code>

<h4>Upload Project TOR Document</h4>
<p>Upload project TOR document of $tpid</p>
<code>http://domain.name/project/tor/{$tpid}/uploadphoto</code>
<p>File :: $_FILES['photo']</p>

<h4>Delete Project TOR Document</h4>
<p>Delete project TOR document of $tpid</p>
<code>http://domain.name/project/tor/{$tpid}/removephoto/{$photoid}?confirm=yes</code>

<h3>Page</h3>

<h4>project.tor</h4>
<p>File :: project.tor.php</p>
<code>R::Page('project.tor',$self,$tpid,$action,$trid)</code>

<h4>project.tor.list</h4>
<p>File :: project.tor.list.php</p>
<code>R::Page('project.tor.list',$self)</code>

<h3>View</h3>

<h4>view.project.tor.view</h4>
<p>File :: view.project.tor.view.php</p>
<code>R::View('project.tor.view',$torInfo)</code>

<h3>Model</h3>

<h4>project.tor.create</h4>
<code>
R::Model('project.tor.create',$tpid);
</code>

<h4>project.tor.get</h4>
<code>
R::Model('project.tor.get',$tpid);
</code>

<h4>project.tor.remove</h4>
<code>
R::Model('project.tor.remove',$tpid);
</code>